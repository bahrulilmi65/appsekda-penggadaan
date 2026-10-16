<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\ReturnItem;
use App\Models\ReturnModel;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = ReturnModel::with(['booking.customer', 'receivedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->paginate(20)->withQueryString();

        return view('admin.returns.index', compact('returns'));
    }

    public function show(ReturnModel $return)
    {
        $return->load(['booking.customer', 'items.facility', 'receivedBy', 'verifiedBy']);
        return view('admin.returns.show', compact('return'));
    }

    public function store(Request $request, Booking $booking)
    {
        $request->validate([
            'return_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array',
            'items.*.booking_item_id' => 'required|exists:booking_items,id',
            'items.*.qty_good' => 'required|integer|min:0',
            'items.*.qty_dirty' => 'required|integer|min:0',
            'items.*.qty_damaged' => 'required|integer|min:0',
            'items.*.qty_lost' => 'required|integer|min:0',
            'items.*.condition_notes' => 'nullable|string',
        ]);

        $returnRecord = DB::transaction(function () use ($request, $booking) {
            $returnRecord = ReturnModel::create([
                'booking_id' => $booking->id,
                'received_by' => auth()->id(),
                'return_date' => $request->return_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $bookingItem = \App\Models\BookingItem::findOrFail($itemData['booking_item_id']);

                $qtyGood = (int) $itemData['qty_good'];
                $qtyDirty = (int) $itemData['qty_dirty'];
                $qtyDamaged = (int) $itemData['qty_damaged'];
                $qtyLost = (int) $itemData['qty_lost'];

                ReturnItem::create([
                    'return_id' => $returnRecord->id,
                    'facility_id' => $bookingItem->facility_id,
                    'booking_item_id' => $bookingItem->id,
                    'quantity_returned' => $qtyGood + $qtyDirty + $qtyDamaged + $qtyLost,
                    'qty_good' => $qtyGood,
                    'qty_dirty' => $qtyDirty,
                    'qty_damaged' => $qtyDamaged,
                    'qty_lost' => $qtyLost,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                ]);

                // Update inventory
                $this->inventoryService->processReturn(
                    $bookingItem->facility_id,
                    $qtyGood, $qtyDirty, $qtyDamaged, $qtyLost,
                    $returnRecord->id
                );

                // Buat laundry record untuk yang kotor
                if ($qtyDirty > 0) {
                    \App\Models\Laundry::create([
                        'facility_id' => $bookingItem->facility_id,
                        'quantity' => $qtyDirty,
                        'date_in' => $request->return_date,
                        'estimated_done' => now()->addDays(3)->toDateString(),
                        'status' => 'pending',
                        'handled_by' => auth()->id(),
                    ]);
                }

                // Buat damage record untuk yang rusak
                if ($qtyDamaged > 0) {
                    \App\Models\Damage::create([
                        'facility_id' => $bookingItem->facility_id,
                        'booking_id' => $booking->id,
                        'quantity' => $qtyDamaged,
                        'damage_type' => 'damage',
                        'description' => 'Rusak saat pengembalian - ' . ($itemData['condition_notes'] ?? ''),
                        'repair_cost' => 0,
                        'status' => 'reported',
                        'reported_date' => $request->return_date,
                        'reported_by' => auth()->id(),
                    ]);
                }

                if ($qtyLost > 0) {
                    \App\Models\Damage::create([
                        'facility_id' => $bookingItem->facility_id,
                        'booking_id' => $booking->id,
                        'quantity' => $qtyLost,
                        'damage_type' => 'loss',
                        'description' => 'Hilang saat pengembalian',
                        'replacement_cost' => 0,
                        'status' => 'reported',
                        'reported_date' => $request->return_date,
                        'reported_by' => auth()->id(),
                    ]);
                }

                $bookingItem->update(['status' => 'returned']);
            }

            // Update return status
            $returnRecord->update(['status' => 'completed']);

            // Update booking status
            app(\App\Services\BookingService::class)->changeStatus(
                $booking, 'return_process', 'Pengembalian barang dicatat'
            );

            return $returnRecord;
        });

        ActivityLog::record('create', 'return', "Mencatat pengembalian {$returnRecord->return_number} untuk booking {$booking->booking_number}");

        return response()->json(['success' => true, 'message' => 'Pengembalian berhasil dicatat.', 'return_id' => $returnRecord->id]);
    }

    public function verify(Request $request, ReturnModel $return)
    {
        $return->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Update booking ke completed jika semua item dikembalikan
        $booking = $return->booking;
        $allReturned = $booking->items()->whereNotIn('status', ['returned', 'completed', 'cancelled'])->count() === 0;

        if ($allReturned) {
            app(\App\Services\BookingService::class)->changeStatus($booking, 'completed', 'Semua barang dikembalikan dan diverifikasi');
        }

        ActivityLog::record('verify', 'return', "Memverifikasi pengembalian {$return->return_number}");

        return response()->json(['success' => true, 'message' => 'Pengembalian berhasil diverifikasi.']);
    }
}
