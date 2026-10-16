<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'facility_id', 'movement_type', 'from_status', 'to_status',
        'quantity', 'qty_before', 'qty_after',
        'reference_type', 'reference_id',
        'performed_by', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'qty_before' => 'integer',
        'qty_after' => 'integer',
    ];

    const MOVEMENT_LABELS = [
        'purchase' => 'Pembelian/Penambahan',
        'booking' => 'Booking',
        'reservation' => 'Reservasi',
        'delivery' => 'Pengantaran',
        'return' => 'Pengembalian',
        'laundry' => 'Laundry Masuk',
        'laundry_complete' => 'Laundry Selesai',
        'damage' => 'Kerusakan',
        'loss' => 'Kehilangan',
        'adjustment' => 'Penyesuaian',
        'maintenance' => 'Maintenance',
        'maintenance_complete' => 'Maintenance Selesai',
        'inspection_pass' => 'Inspeksi Lulus',
        'inspection_fail' => 'Inspeksi Gagal',
        'cancel' => 'Pembatalan',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference()
    {
        return $this->morphTo('reference');
    }

    public function getMovementLabelAttribute(): string
    {
        return self::MOVEMENT_LABELS[$this->movement_type] ?? $this->movement_type;
    }
}
