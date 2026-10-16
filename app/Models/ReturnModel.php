<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_number', 'booking_id', 'delivery_id', 'received_by',
        'return_date', 'status', 'notes', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'verified_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => ['label' => 'Menunggu', 'color' => 'warning'],
        'partial' => ['label' => 'Sebagian', 'color' => 'info'],
        'completed' => ['label' => 'Selesai', 'color' => 'success'],
        'verified' => ['label' => 'Diverifikasi', 'color' => 'success'],
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->return_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->return_number = 'RTN/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
