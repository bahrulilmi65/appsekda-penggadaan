<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index()
    {
        $customer = auth()->user()->customer;
        $payments = Payment::with(['booking'])
            ->where('customer_id', $customer?->id)
            ->latest()->paginate(10);

        return view('user.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        if ($payment->customer_id !== auth()->user()->customer?->id) {
            abort(403);
        }

        $payment->load(['booking.items.facility', 'invoice']);
        return view('user.payments.show', compact('payment'));
    }

    public function uploadProof(Request $request, Payment $payment)
    {
        if ($payment->customer_id !== auth()->user()->customer?->id) {
            abort(403);
        }

        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'payment_method' => 'required|in:transfer,cash,virtual_account,qris,lainnya',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'account_name' => 'nullable|string|max:100',
            'transaction_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount_paid' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->paymentService->uploadProof($payment, $request->file('proof_of_payment'), $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dari admin.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function downloadInvoice(Invoice $invoice)
    {
        if ($invoice->customer_id !== auth()->user()->customer?->id) {
            abort(403);
        }

        $invoice->load(['booking.customer', 'booking.items.facility', 'customer', 'payment']);
        $settings = \App\Models\Setting::getGroup('general');
        $bankSettings = \App\Models\Setting::getGroup('payment');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings', 'bankSettings'))->setPaper('A4');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
