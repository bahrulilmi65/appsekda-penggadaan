<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $query = Payment::with(['booking.customer', 'verifiedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('booking.customer', fn($q2) => $q2->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        // Summary stats
        $stats = [
            'total_tagihan' => Payment::sum('amount_due'),
            'total_bayar' => Payment::where('status', 'paid')->sum('amount_paid'),
            'belum_bayar' => Payment::where('status', 'unpaid')->sum('amount_due'),
            'menunggu_verifikasi' => Payment::where('status', 'waiting_verification')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['booking.customer', 'booking.items.facility', 'verifiedBy', 'invoice']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        if ($payment->status !== 'waiting_verification') {
            return response()->json(['success' => false, 'message' => 'Pembayaran ini tidak dapat diverifikasi.'], 422);
        }

        $this->paymentService->verifyPayment($payment, true, $request->notes);

        return response()->json(['success' => true, 'message' => 'Pembayaran berhasil diverifikasi.']);
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        if ($payment->status !== 'waiting_verification') {
            return response()->json(['success' => false, 'message' => 'Pembayaran ini tidak dapat ditolak.'], 422);
        }

        $this->paymentService->verifyPayment($payment, false, $request->reason);

        return response()->json(['success' => true, 'message' => 'Pembayaran berhasil ditolak.']);
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load([
            'booking.customer', 'booking.items.facility',
            'customer', 'payment',
        ]);

        $settings = \App\Models\Setting::getGroup('general');
        $bankSettings = \App\Models\Setting::getGroup('payment');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings', 'bankSettings'));
        $pdf->setPaper('A4');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
