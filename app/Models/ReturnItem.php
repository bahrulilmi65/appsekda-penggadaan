<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'return_items';

    protected $fillable = [
        'return_id', 'facility_id', 'booking_item_id',
        'quantity_returned', 'qty_good', 'qty_dirty',
        'qty_damaged', 'qty_lost', 'condition_notes', 'photo',
    ];

    protected $casts = [
        'quantity_returned' => 'integer',
        'qty_good' => 'integer',
        'qty_dirty' => 'integer',
        'qty_damaged' => 'integer',
        'qty_lost' => 'integer',
    ];

    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function bookingItem()
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
