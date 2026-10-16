<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    protected $table = 'laundry';

    protected $fillable = [
        'laundry_number', 'facility_id', 'return_item_id', 'quantity',
        'date_in', 'estimated_done', 'date_done', 'status',
        'laundry_vendor', 'laundry_cost', 'notes', 'handled_by',
    ];

    protected $casts = [
        'date_in' => 'date',
        'estimated_done' => 'date',
        'date_done' => 'date',
        'quantity' => 'integer',
        'laundry_cost' => 'decimal:2',
    ];

    const STATUSES = [
        'pending' => ['label' => 'Menunggu', 'color' => 'secondary'],
        'in_laundry' => ['label' => 'Dalam Laundry', 'color' => 'warning'],
        'done' => ['label' => 'Selesai', 'color' => 'info'],
        'returned' => ['label' => 'Dikembalikan', 'color' => 'success'],
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function returnItem()
    {
        return $this->belongsTo(ReturnItem::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->laundry_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->laundry_number = 'LDY/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
