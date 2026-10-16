<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'facility_id', 'quantity', 'unit_price',
        'price_type', 'duration', 'duration_unit', 'subtotal', 'notes', 'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'duration' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    const STATUSES = [
        'reserved' => ['label' => 'Dipesan', 'color' => 'warning'],
        'preparing' => ['label' => 'Persiapan', 'color' => 'info'],
        'in_delivery' => ['label' => 'Dikirim', 'color' => 'primary'],
        'in_use' => ['label' => 'Digunakan', 'color' => 'success'],
        'returned' => ['label' => 'Dikembalikan', 'color' => 'secondary'],
        'completed' => ['label' => 'Selesai', 'color' => 'success'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'danger'],
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
