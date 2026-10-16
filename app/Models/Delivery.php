<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_number', 'booking_id', 'type',
        'destination_address', 'destination_person', 'destination_phone',
        'assigned_to', 'status', 'scheduled_at', 'departed_at', 'arrived_at',
        'notes', 'vehicle', 'driver_name',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'departed_at' => 'datetime',
        'arrived_at' => 'datetime',
    ];

    const STATUSES = [
        'waiting' => ['label' => 'Menunggu', 'color' => 'secondary'],
        'preparing' => ['label' => 'Persiapan', 'color' => 'info'],
        'ready_to_deliver' => ['label' => 'Siap Kirim', 'color' => 'warning'],
        'in_delivery' => ['label' => 'Dalam Pengantaran', 'color' => 'primary'],
        'delivered' => ['label' => 'Terkirim', 'color' => 'success'],
        'returning' => ['label' => 'Kembali', 'color' => 'info'],
        'returned' => ['label' => 'Dikembalikan', 'color' => 'success'],
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->delivery_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->delivery_number = 'DLV/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
