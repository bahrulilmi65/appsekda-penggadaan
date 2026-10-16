<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Damage;
use App\Models\Laundry;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function booking(Request $request)
    {
        $query = Booking::with(['customer', 'items.facility'])->latest();
        $this->applyDateFilter($query, $request, 'start_date');
        if ($request->filled('status')) $query->where('status', $request->status);

        $bookings = $query->paginate(50)->withQueryString();
        $stats = [
            'total' => Booking::count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'active' => Booking::whereIn('status', ['approved', 'paid', 'preparing', 'in_use'])->count(),
        ];

        return view('admin.reports.booking', compact('bookings', 'stats'));
    }

    public function payment(Request $request)
    {
        $query = Payment::with(['booking.customer'])->latest();
        $this->applyDateFilter($query, $request, 'payment_date');
        if ($request->filled('status')) $query->where('status', $request->status);

        $payments = $query->paginate(50)->withQueryString();
        $stats = [
            'total_tagihan' => $query->clone()->sum('amount_due'),
            'total_bayar' => Payment::where('status', 'paid')->whereBetween('payment_date', [$request->date_from ?? '2020-01-01', $request->date_to ?? now()])->sum('amount_paid'),
            'belum_bayar' => Payment::where('status', 'unpaid')->sum('amount_due'),
            'menunggu_verifikasi' => Payment::where('status', 'waiting_verification')->count(),
        ];

        return view('admin.reports.payment', compact('payments', 'stats'));
    }

    public function inventory(Request $request)
    {
        $facilities = \App\Models\Facility::with(['inventory', 'category'])
            ->active()->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.reports.inventory', compact('facilities'));
    }

    public function facility(Request $request)
    {
        $data = \Illuminate\Support\Facades\DB::table('booking_items')
            ->select(
                'facility_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as booking_count'),
                \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('SUM(booking_items.subtotal) as total_revenue')
            )
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->whereNotIn('bookings.status', ['cancelled', 'expired', 'rejected'])
            ->groupBy('facility_id')
            ->orderByDesc('booking_count')
            ->paginate(50)
            ->withQueryString();

        // Preload facility sekali (hindari N+1 per baris)
        $facilities = \App\Models\Facility::whereIn('id', $data->pluck('facility_id'))->get()->keyBy('id');
        $data->getCollection()->transform(function ($item) use ($facilities) {
            $item->facility = $facilities->get($item->facility_id);
            return $item;
        });

        return view('admin.reports.facility', compact('data'));
    }

    public function damage(Request $request)
    {
        $query = Damage::with(['facility', 'booking.customer', 'reportedBy'])->latest();
        $this->applyDateFilter($query, $request, 'reported_date');
        $damages = $query->paginate(50)->withQueryString();
        return view('admin.reports.damage', compact('damages'));
    }

    public function laundry(Request $request)
    {
        $query = Laundry::with(['facility', 'handledBy'])->latest();
        $this->applyDateFilter($query, $request, 'date_in');
        $laundries = $query->paginate(50)->withQueryString();
        return view('admin.reports.laundry', compact('laundries'));
    }

    public function customer(Request $request)
    {
        $customers = \App\Models\Customer::with(['user'])
            ->withCount('bookings')->latest()->paginate(50)->withQueryString();
        return view('admin.reports.customer', compact('customers'));
    }

    public function bookingPdf(Request $request)
    {
        $bookings = Booking::with(['customer', 'items.facility'])->latest()->get();
        $settings = \App\Models\Setting::getGroup('general');
        $pdf = Pdf::loadView('pdf.report-booking', compact('bookings', 'settings'))->setPaper('A4', 'landscape');
        return $pdf->download('Laporan-Booking-' . now()->format('Ymd') . '.pdf');
    }

    public function paymentPdf(Request $request)
    {
        $payments = Payment::with(['booking.customer'])->latest()->get();
        $settings = \App\Models\Setting::getGroup('general');
        $pdf = Pdf::loadView('pdf.report-payment', compact('payments', 'settings'))->setPaper('A4', 'landscape');
        return $pdf->download('Laporan-Pembayaran-' . now()->format('Ymd') . '.pdf');
    }

    public function bookingExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BookingExport($request->all()),
            'Laporan-Booking-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function paymentExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PaymentExport($request->all()),
            'Laporan-Pembayaran-' . now()->format('Ymd') . '.xlsx'
        );
    }

    protected function applyDateFilter($query, Request $request, string $column): void
    {
        if ($request->filled('date_from')) {
            $query->where($column, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where($column, '<=', $request->date_to);
        }
    }
}
