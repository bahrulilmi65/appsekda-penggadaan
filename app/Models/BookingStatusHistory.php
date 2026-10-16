<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    protected $fillable = [
        'booking_id', 'from_status', 'to_status', 'changed_by', 'notes',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromLabelAttribute(): string
    {
        return Booking::STATUSES[$this->from_status]['label'] ?? $this->from_status ?? '-';
    }

    public function getToLabelAttribute(): string
    {
        return Booking::STATUSES[$this->to_status]['label'] ?? $this->to_status;
    }
}
