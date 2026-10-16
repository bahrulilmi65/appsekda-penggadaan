<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Update inventory quantities with movement tracking.
     * SEMUA perubahan stok harus melalui method ini.
     *
     * @param int $facilityId
     * @param string $fromStatus  null jika penambahan baru
     * @param string $toStatus
     * @param int $quantity
     * @param string $movementType
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     */
    public function moveInventory(
        int $facilityId,
        ?string $fromStatus,
        string $toStatus,
        int $quantity,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): InventoryMovement {
        return DB::transaction(function () use (
            $facilityId, $fromStatus, $toStatus, $quantity,
            $movementType, $referenceType, $referenceId, $notes
        ) {
            $inventory = Inventory::where('facility_id', $facilityId)->lockForUpdate()->firstOrFail();

            $qtyBefore = $inventory->{"qty_{$toStatus}"} ?? 0;

            // Kurangi dari status asal
            if ($fromStatus && $fromStatus !== 'new') {
                $fromField = "qty_{$fromStatus}";
                if (isset($inventory->$fromField)) {
                    $inventory->$fromField = max(0, $inventory->$fromField - $quantity);
                }
            }

            // Tambahkan ke status tujuan
            $toField = "qty_{$toStatus}";
            if (isset($inventory->$toField)) {
                $inventory->$toField += $quantity;
            }

            // Jika menambah stok baru (purchase/adjustment), tambah total juga
            if ($movementType === 'purchase' || ($movementType === 'adjustment' && !$fromStatus)) {
                $inventory->qty_total += $quantity;
            }

            // Jika write-off (hilang permanen dari total)
            if (in_array($movementType, ['loss']) && $fromStatus === 'lost') {
                // qty_lost sudah bertambah, jangan kurangi total di sini
                // total tetap untuk histori, hanya available yang berkurang
            }

            $inventory->last_updated_at = now();
            $inventory->recalculate(); // recalculate available

            $qtyAfter = $inventory->{"qty_{$toStatus}"} ?? 0;

            // Catat movement
            $movement = InventoryMovement::create([
                'facility_id' => $facilityId,
                'movement_type' => $movementType,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'quantity' => $quantity,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => auth()->id(),
                'notes' => $notes,
            ]);

            return $movement;
        });
    }

    /**
     * Reserve stok saat booking dibuat
     */
    public function reserveStock(int $facilityId, int $quantity, int $bookingId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'available',
            'reserved',
            $quantity,
            'reservation',
            'booking',
            $bookingId,
            'Reserved for booking #' . $bookingId
        );
    }

    /**
     * Lepas reservasi (cancel booking atau expired)
     */
    public function releaseReservation(int $facilityId, int $quantity, int $bookingId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'reserved',
            'available',
            $quantity,
            'cancel',
            'booking',
            $bookingId,
            'Released reservation for booking #' . $bookingId
        );
    }

    /**
     * Tandai dalam persiapan
     */
    public function markPreparing(int $facilityId, int $quantity, int $bookingId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'reserved',
            'preparing',
            $quantity,
            'delivery',
            'booking',
            $bookingId,
            'Preparing for delivery'
        );
    }

    /**
     * Tandai dalam pengantaran
     */
    public function markInDelivery(int $facilityId, int $quantity, int $deliveryId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'preparing',
            'in_delivery',
            $quantity,
            'delivery',
            'delivery',
            $deliveryId,
            'In delivery'
        );
    }

    /**
     * Tandai sedang digunakan
     */
    public function markInUse(int $facilityId, int $quantity, int $deliveryId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'in_delivery',
            'in_use',
            $quantity,
            'delivery',
            'delivery',
            $deliveryId,
            'Delivered and in use'
        );
    }

    /**
     * Proses pengembalian - pisahkan ke berbagai status
     */
    public function processReturn(
        int $facilityId,
        int $qtyGood,
        int $qtyDirty,
        int $qtyDamaged,
        int $qtyLost,
        int $returnId
    ): void {
        $totalReturned = $qtyGood + $qtyDirty + $qtyDamaged + $qtyLost;

        // Good → available
        if ($qtyGood > 0) {
            $this->moveInventory($facilityId, 'in_use', 'available', $qtyGood, 'return', 'return', $returnId, 'Good condition');
        }

        // Dirty → laundry
        if ($qtyDirty > 0) {
            $this->moveInventory($facilityId, 'in_use', 'laundry', $qtyDirty, 'laundry', 'return', $returnId, 'Needs laundry');
        }

        // Damaged → damaged
        if ($qtyDamaged > 0) {
            $this->moveInventory($facilityId, 'in_use', 'damaged', $qtyDamaged, 'damage', 'return', $returnId, 'Damaged on return');
        }

        // Lost - tidak dikembalikan, stok in_use dikurangi
        if ($qtyLost > 0) {
            $this->moveInventory($facilityId, 'in_use', 'lost', $qtyLost, 'loss', 'return', $returnId, 'Lost/not returned');
        }
    }

    /**
     * Laundry selesai → kembali available
     */
    public function completeLaundry(int $facilityId, int $quantity, int $laundryId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'laundry',
            'available',
            $quantity,
            'laundry_complete',
            'laundry',
            $laundryId,
            'Laundry completed'
        );
    }

    /**
     * Maintenance selesai → kembali available
     */
    public function completeMaintenance(int $facilityId, int $quantity, int $referenceId): InventoryMovement
    {
        return $this->moveInventory(
            $facilityId,
            'maintenance',
            'available',
            $quantity,
            'maintenance_complete',
            'damage',
            $referenceId,
            'Maintenance completed'
        );
    }

    /**
     * Tambah stok baru
     */
    public function addStock(int $facilityId, int $quantity, ?string $notes = null): InventoryMovement
    {
        // Update facility total_stock juga
        Facility::where('id', $facilityId)->increment('total_stock', $quantity);

        return $this->moveInventory(
            $facilityId,
            null,
            'available',
            $quantity,
            'purchase',
            null,
            null,
            $notes ?? 'Stock addition'
        );
    }

    /**
     * Adjustment stok
     */
    public function adjustStock(int $facilityId, string $status, int $newQty, ?string $notes = null): InventoryMovement
    {
        $inventory = Inventory::where('facility_id', $facilityId)->firstOrFail();
        $field = "qty_{$status}";
        $currentQty = $inventory->$field ?? 0;
        $diff = $newQty - $currentQty;

        if ($diff === 0) {
            // Tidak ada perubahan, buat dummy movement
            return InventoryMovement::create([
                'facility_id' => $facilityId,
                'movement_type' => 'adjustment',
                'from_status' => $status,
                'to_status' => $status,
                'quantity' => 0,
                'qty_before' => $currentQty,
                'qty_after' => $currentQty,
                'performed_by' => auth()->id(),
                'notes' => 'No change - ' . ($notes ?? ''),
            ]);
        }

        return $this->moveInventory(
            $facilityId,
            $diff > 0 ? null : $status,
            $status,
            abs($diff),
            'adjustment',
            null,
            null,
            $notes ?? 'Manual stock adjustment'
        );
    }
}
