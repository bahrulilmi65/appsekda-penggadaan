<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Facility;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService) {}

    public function index()
    {
        $carts = Cart::with('facility.inventory')->where('user_id', auth()->id())->get();
        return view('user.cart.index', compact('carts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $facility = Facility::with('inventory')->findOrFail($request->facility_id);

        if (!$facility->is_active || !$facility->is_bookable) {
            return response()->json(['success' => false, 'message' => 'Fasilitas tidak dapat dipesan.'], 422);
        }

        // Cek ketersediaan jika tanggal diisi
        if ($request->start_date && $request->end_date) {
            if (!$facility->isBuilding()) {
                $available = $this->availabilityService->getAvailableQuantity(
                    $facility->id, $request->start_date, $request->end_date
                );
                if ($available < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak mencukupi. Tersedia: {$available} {$facility->unit}, diminta: {$request->quantity} {$facility->unit}.",
                    ], 422);
                }
            }
        }

        // Tambah atau update cart
        $existing = Cart::where('user_id', auth()->id())->where('facility_id', $request->facility_id)->first();

        if ($existing) {
            $existing->update([
                'quantity' => $request->quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'notes' => $request->notes,
            ]);
            $cart = $existing;
        } else {
            $cart = Cart::create([
                'user_id' => auth()->id(),
                'facility_id' => $request->facility_id,
                'quantity' => $request->quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'notes' => $request->notes,
            ]);
        }

        $cartCount = Cart::where('user_id', auth()->id())->count();

        return response()->json([
            'success' => true,
            'message' => "{$facility->name} berhasil ditambahkan ke keranjang.",
            'cart_count' => $cartCount,
        ]);
    }

    public function update(Request $request, Cart $cart)
    {
        $this->authorize('update', $cart);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'message' => 'Keranjang berhasil diperbarui.']);
    }

    public function destroy(Cart $cart)
    {
        $this->authorize('delete', $cart);
        $cart->delete();

        $cartCount = Cart::where('user_id', auth()->id())->count();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang.',
            'cart_count' => $cartCount,
        ]);
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();
        return response()->json(['success' => true, 'message' => 'Keranjang berhasil dikosongkan.']);
    }

    public function count()
    {
        return response()->json(['count' => Cart::where('user_id', auth()->id())->count()]);
    }
}
