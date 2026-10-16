<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    protected $fillable = [
        'delivery_id', 'facility_id', 'booking_item_id',
        'quantity_sent', 'quantity_received', 'notes', 'condition',
    ];

    protected $casts = [
        'quantity_sent' => 'integer',
        'quantity_received' => 'integer',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function bookingItem()
    {
        return $this->belongsTo(BookingItem::class);
    }
}
