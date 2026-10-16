<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'facility_id',
        'qty_available', 'qty_reserved', 'qty_preparing',
        'qty_in_delivery', 'qty_in_use', 'qty_returned',
        'qty_inspection', 'qty_laundry', 'qty_damaged',
        'qty_lost', 'qty_maintenance', 'qty_total',
        'last_updated_at',
    ];

    protected $casts = [
        'qty_available' => 'integer',
        'qty_reserved' => 'integer',
        'qty_preparing' => 'integer',
        'qty_in_delivery' => 'integer',
        'qty_in_use' => 'integer',
        'qty_returned' => 'integer',
        'qty_inspection' => 'integer',
        'qty_laundry' => 'integer',
        'qty_damaged' => 'integer',
        'qty_lost' => 'integer',
        'qty_maintenance' => 'integer',
        'qty_total' => 'integer',
        'last_updated_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Jumlah tidak tersedia (semua status kecuali available)
     */
    public function getUnavailableAttribute(): int
    {
        return $this->qty_reserved
            + $this->qty_preparing
            + $this->qty_in_delivery
            + $this->qty_in_use
            + $this->qty_returned
            + $this->qty_inspection
            + $this->qty_laundry
            + $this->qty_damaged
            + $this->qty_lost
            + $this->qty_maintenance;
    }

    /**
     * Recalculate available based on total - all non-available
     */
    public function recalculate(): void
    {
        $used = $this->qty_reserved
            + $this->qty_preparing
            + $this->qty_in_delivery
            + $this->qty_in_use
            + $this->qty_returned
            + $this->qty_inspection
            + $this->qty_laundry
            + $this->qty_damaged
            + $this->qty_lost
            + $this->qty_maintenance;

        $this->qty_available = max(0, $this->qty_total - $used);
        $this->last_updated_at = now();
        $this->save();
    }
}
