<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\Customer;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request)
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return redirect()->route('user.profile')->with('warning', 'Lengkapi profil Anda terlebih dahulu.');
        }

        $query = $customer->bookings()->with(['items.facility'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('user.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $user = auth()->user();
        $customer = $user->customer;

        if (!$customer) {
            return redirect()->route('user.profile')->with('warning', 'Lengkapi profil Anda terlebih dahulu.');
        }

        if (!$customer->isVerified()) {
            return redirect()->route('user.cart.index')
                ->with('warning', 'Akun Anda belum diverifikasi. Silakan tunggu verifikasi dari admin.');
        }

        $carts = Cart::with(['facility.inventory', 'facility.primaryImage'])
            ->where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return redirect()->route('catalog')->with('info', 'Keranjang Anda kosong. Silakan pilih fasilitas terlebih dahulu.');
        }

        return view('user.bookings.create', compact('carts', 'customer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:500',
            'participants' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'exists:carts,id',
        ]);

        $user = auth()->user();
        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil penyewa tidak ditemukan.'], 422);
        }

        if (!$customer->isVerified()) {
            return response()->json(['success' => false, 'message' => 'Akun Anda belum diverifikasi oleh admin.'], 422);
        }

        try {
            $booking = $this->bookingService->createFromCart(
                $user->id,
                $customer->id,
                $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'purpose', 'participants', 'notes', 'special_request']),
                $request->cart_ids
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat! Silakan tunggu persetujuan admin.',
                'booking_id' => $booking->id,
                'redirect' => route('user.bookings.show', $booking->id),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show(Booking $booking)
    {
        // Hanya pemilik booking yang bisa melihat
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load([
            'customer', 'items.facility', 'statusHistories',
            'payment', 'invoice', 'delivery',
        ]);

        return view('user.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return response()->json(['success' => false, 'message' => 'Booking tidak dapat dibatalkan pada status ini.'], 422);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $this->bookingService->changeStatus($booking, 'cancelled', $request->reason ?? 'Dibatalkan oleh penyewa');

        return response()->json(['success' => true, 'message' => 'Booking berhasil dibatalkan.']);
    }
}
