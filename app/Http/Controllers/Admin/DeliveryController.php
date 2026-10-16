<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = Delivery::with(['booking.customer', 'assignedTo'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['booking.customer', 'items.facility', 'items.bookingItem', 'assignedTo']);
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function store(Request $request, Booking $booking)
    {
        $request->validate([
            'type' => 'required|in:delivery,pickup',
            'destination_address' => 'nullable|string|max:500',
            'destination_person' => 'nullable|string|max:100',
            'destination_phone' => 'nullable|string|max:20',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
            'vehicle' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array',
            'items.*.booking_item_id' => 'required|exists:booking_items,id',
            'items.*.quantity_sent' => 'required|integer|min:1',
        ]);

        $delivery = DB::transaction(function () use ($request, $booking) {
            $delivery = Delivery::create([
                'booking_id' => $booking->id,
                'type' => $request->type,
                'destination_address' => $request->destination_address,
                'destination_person' => $request->destination_person,
                'destination_phone' => $request->destination_phone,
                'assigned_to' => $request->assigned_to,
                'scheduled_at' => $request->scheduled_at,
                'vehicle' => $request->vehicle,
                'driver_name' => $request->driver_name,
                'notes' => $request->notes,
                'status' => 'preparing',
            ]);

            foreach ($request->items as $item) {
                $bookingItem = \App\Models\BookingItem::findOrFail($item['booking_item_id']);

                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'facility_id' => $bookingItem->facility_id,
                    'booking_item_id' => $bookingItem->id,
                    'quantity_sent' => $item['quantity_sent'],
                ]);

                // Update inventory: reserved → preparing
                $this->inventoryService->markPreparing(
                    $bookingItem->facility_id,
                    $item['quantity_sent'],
                    $booking->id
                );

                $bookingItem->update(['status' => 'preparing']);
            }

            // Update booking status
            app(\App\Services\BookingService::class)->changeStatus($booking, 'preparing', 'Persiapan pengiriman dibuat');

            return $delivery;
        });

        ActivityLog::record('create', 'delivery', "Membuat pengiriman {$delivery->delivery_number} untuk booking {$booking->booking_number}");

        return response()->json(['success' => true, 'message' => 'Pengiriman berhasil dibuat.', 'delivery_id' => $delivery->id]);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $request->validate([
            'status' => 'required|in:preparing,ready_to_deliver,in_delivery,delivered,returning,returned',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $delivery) {
            $oldStatus = $delivery->status;
            $newStatus = $request->status;

            $delivery->update([
                'status' => $newStatus,
                'notes' => $request->notes ?? $delivery->notes,
                'departed_at' => $newStatus === 'in_delivery' ? now() : $delivery->departed_at,
                'arrived_at' => $newStatus === 'delivered' ? now() : $delivery->arrived_at,
            ]);

            // Update inventory status
            if ($newStatus === 'in_delivery' && $oldStatus !== 'in_delivery') {
                foreach ($delivery->items as $item) {
                    $this->inventoryService->markInDelivery(
                        $item->facility_id, $item->quantity_sent, $delivery->id
                    );
                    $item->bookingItem->update(['status' => 'in_delivery']);
                }
                app(\App\Services\BookingService::class)->changeStatus($delivery->booking, 'in_delivery', 'Barang dalam pengantaran');
            }

            if ($newStatus === 'delivered') {
                foreach ($delivery->items as $item) {
                    $this->inventoryService->markInUse(
                        $item->facility_id, $item->quantity_sent, $delivery->id
                    );
                    $item->bookingItem->update(['status' => 'in_use']);
                }
                app(\App\Services\BookingService::class)->changeStatus($delivery->booking, 'in_use', 'Barang terkirim dan sedang digunakan');
            }
        });

        ActivityLog::record('update', 'delivery', "Status pengiriman {$delivery->delivery_number} → {$request->status}");

        return response()->json(['success' => true, 'message' => 'Status pengiriman berhasil diubah.']);
    }
}
