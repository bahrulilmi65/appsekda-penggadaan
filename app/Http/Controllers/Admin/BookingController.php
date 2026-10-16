<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Facility;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'items.facility'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('booking_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', '%' . $request->search . '%'));
            });
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer', 'user', 'items.facility', 'statusHistories.changedBy',
            'payment', 'invoice', 'delivery', 'approvedBy',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        if (!in_array($booking->status, ['waiting_approval', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Status booking tidak dapat disetujui.'], 422);
        }

        $this->bookingService->changeStatus($booking, 'approved', $request->notes ?? 'Booking disetujui oleh ' . auth()->user()->name);

        // Buat payment record
        app(\App\Services\PaymentService::class)->createPayment($booking);

        $booking->update([
            'status' => 'waiting_payment',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ActivityLog::record('approve', 'booking', "Menyetujui booking {$booking->booking_number}");

        return response()->json(['success' => true, 'message' => 'Booking berhasil disetujui.']);
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        if (!in_array($booking->status, ['waiting_approval', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Status booking tidak dapat ditolak.'], 422);
        }

        $booking->update(['rejection_reason' => $request->reason]);
        $this->bookingService->changeStatus($booking, 'rejected', $request->reason);

        ActivityLog::record('reject', 'booking', "Menolak booking {$booking->booking_number}: {$request->reason}");

        return response()->json(['success' => true, 'message' => 'Booking berhasil ditolak.']);
    }

    public function changeStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:preparing,in_delivery,in_use,return_process,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->bookingService->changeStatus($booking, $request->status, $request->notes);

        ActivityLog::record('update', 'booking', "Mengubah status booking {$booking->booking_number} ke {$request->status}");

        return response()->json(['success' => true, 'message' => 'Status booking berhasil diubah.']);
    }

    public function storeManual(Request $request)
    {
        // Booking manual oleh admin
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'purpose' => 'required|string|max:500',
            'facilities' => 'required|array|min:1',
            'facilities.*.facility_id' => 'required|exists:facilities,id',
            'facilities.*.quantity' => 'required|integer|min:1',
        ]);

        // Buat customer user jika tidak ada
        $customer = Customer::with('user')->findOrFail($request->customer_id);

        if (!$customer->user_id) {
            return response()->json(['success' => false, 'message' => 'Customer tidak memiliki akun user.'], 422);
        }

        // Buat cart sementara
        foreach ($request->facilities as $item) {
            \App\Models\Cart::updateOrCreate(
                ['user_id' => $customer->user_id, 'facility_id' => $item['facility_id']],
                [
                    'quantity' => $item['quantity'],
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]
            );
        }

        $cartIds = \App\Models\Cart::where('user_id', $customer->user_id)->pluck('id')->toArray();

        $booking = $this->bookingService->createFromCart(
            $customer->user_id,
            $customer->id,
            $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'purpose', 'participants', 'notes']),
            $cartIds
        );

        // Auto approve untuk booking manual admin
        $this->bookingService->changeStatus($booking, 'approved', 'Booking manual oleh admin');
        app(\App\Services\PaymentService::class)->createPayment($booking);
        $booking->update(['status' => 'waiting_payment']);

        return response()->json(['success' => true, 'message' => 'Booking manual berhasil dibuat.', 'booking_id' => $booking->id]);
    }
}
