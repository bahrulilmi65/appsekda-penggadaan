<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id', 'facility_id', 'quantity',
        'start_date', 'end_date', 'start_time', 'end_time', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function getDurationDaysAttribute(): int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 1;
    }

    public function getSubtotalAttribute(): float
    {
        if (!$this->facility) return 0;
        $duration = $this->duration_days;
        $qty = $this->facility->price_type === 'per_item' ? $this->quantity : 1;
        return $this->facility->price * $qty * $duration;
    }
}
