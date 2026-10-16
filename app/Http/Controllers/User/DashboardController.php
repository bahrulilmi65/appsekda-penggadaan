<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $customer = $user->customer;

        $stats = [
            'total_bookings' => $customer?->bookings()->count() ?? 0,
            'active_bookings' => $customer?->bookings()->whereIn('status', ['approved', 'waiting_payment', 'paid', 'preparing', 'in_delivery', 'in_use'])->count() ?? 0,
            'completed_bookings' => $customer?->bookings()->where('status', 'completed')->count() ?? 0,
            'pending_payment' => $customer?->bookings()->where('payment_status', 'unpaid')->whereIn('status', ['approved', 'waiting_payment'])->count() ?? 0,
        ];

        $recentBookings = $customer?->bookings()
            ->with(['items.facility'])
            ->latest()->take(5)->get() ?? collect();

        $unreadNotifications = $user->notifications()->where('is_read', false)->latest()->take(5)->get();

        return view('user.dashboard', compact('stats', 'recentBookings', 'unreadNotifications', 'customer'));
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);

        return view('user.notifications', compact('notifications'));
    }

    public function markRead(int $id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Semua notifikasi telah ditandai dibaca.']);
    }
}
