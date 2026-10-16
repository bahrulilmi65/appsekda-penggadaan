<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Facility;
use App\Models\Laundry;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats cards
        $stats = [
            'total_facilities' => Facility::active()->count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::whereIn('status', ['approved', 'waiting_payment', 'paid', 'preparing', 'in_delivery', 'in_use'])->count(),
            'waiting_payment' => Booking::where('payment_status', 'waiting_verification')->count(),
            'revenue_month' => Payment::where('status', 'paid')
                ->whereMonth('verified_at', now()->month)
                ->whereYear('verified_at', now()->year)
                ->sum('amount_paid'),
            'in_use' => Booking::where('status', 'in_use')->count(),
            'in_delivery' => Delivery::whereIn('status', ['in_delivery', 'ready_to_deliver'])->count(),
            'in_laundry' => Laundry::whereIn('status', ['pending', 'in_laundry'])->sum('quantity'),
        ];

        // Recent bookings
        $recentBookings = Booking::with(['customer', 'items.facility'])
            ->latest()->take(10)->get();

        // Pending approvals
        $pendingApprovals = Booking::where('status', 'waiting_approval')
            ->with('customer')->latest()->take(5)->get();

        // Waiting verification payments
        $waitingVerification = Payment::where('status', 'waiting_verification')
            ->with(['booking.customer'])->latest()->take(5)->get();

        // Chart data - bookings per month (last 12 months)
        $bookingChart = Booking::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        // Revenue chart
        $revenueChart = Payment::select(
            DB::raw('MONTH(verified_at) as month'),
            DB::raw('YEAR(verified_at) as year'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->where('status', 'paid')
            ->where('verified_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        // Top facilities
        $topFacilities = DB::table('booking_items')
            ->select('facility_id', DB::raw('COUNT(*) as booking_count'), DB::raw('SUM(booking_items.subtotal) as total_revenue'))
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->whereNotIn('bookings.status', ['cancelled', 'expired', 'rejected'])
            ->groupBy('facility_id')
            ->orderByDesc('booking_count')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->facility = Facility::find($item->facility_id);
                return $item;
            });

        // Booking status distribution
        $bookingStatus = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return view('admin.dashboard.index', compact(
            'stats', 'recentBookings', 'pendingApprovals', 'waitingVerification',
            'bookingChart', 'revenueChart', 'topFacilities', 'bookingStatus'
        ));
    }

    public function stats()
    {
        return response()->json([
            'active_bookings' => Booking::whereIn('status', ['approved', 'waiting_payment', 'paid', 'preparing', 'in_delivery', 'in_use'])->count(),
            'waiting_approval' => Booking::where('status', 'waiting_approval')->count(),
            'waiting_payment' => Booking::where('payment_status', 'waiting_verification')->count(),
            'in_laundry' => Laundry::whereIn('status', ['pending', 'in_laundry'])->sum('quantity'),
        ]);
    }

    public function notificationCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotificationsCount(),
        ]);
    }

    public function markAllRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai dibaca.']);
    }
}
