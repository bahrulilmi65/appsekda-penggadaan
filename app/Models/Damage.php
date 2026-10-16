<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Damage extends Model
{
    protected $fillable = [
        'damage_number', 'facility_id', 'booking_id', 'return_item_id',
        'quantity', 'damage_type', 'description', 'photo',
        'repair_cost', 'replacement_cost', 'status',
        'reported_date', 'repaired_date', 'notes', 'reported_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'repair_cost' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'reported_date' => 'date',
        'repaired_date' => 'date',
    ];

    const STATUSES = [
        'reported' => ['label' => 'Dilaporkan', 'color' => 'danger'],
        'in_repair' => ['label' => 'Diperbaiki', 'color' => 'warning'],
        'repaired' => ['label' => 'Selesai Diperbaiki', 'color' => 'success'],
        'written_off' => ['label' => 'Dihapuskan', 'color' => 'secondary'],
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function returnItem()
    {
        return $this->belongsTo(ReturnItem::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->damage_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->damage_number = 'DMG/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
