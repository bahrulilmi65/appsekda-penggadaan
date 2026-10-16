<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'booking_id', 'payment_id', 'customer_id',
        'invoice_date', 'due_date', 'subtotal', 'discount', 'additional_fee', 'total',
        'status', 'qr_code', 'verification_token', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'additional_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->invoice_number)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)->count() + 1;
                $model->invoice_number = 'INV/' . $year . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
            if (empty($model->verification_token)) {
                $model->verification_token = Str::random(32);
            }
        });
    }
}
