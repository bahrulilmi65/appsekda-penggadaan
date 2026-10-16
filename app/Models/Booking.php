<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number', 'customer_id', 'user_id',
        'booking_date', 'start_date', 'end_date', 'start_time', 'end_time',
        'purpose', 'participants', 'notes', 'special_request',
        'subtotal', 'discount_amount', 'additional_fee', 'additional_fee_notes', 'total_amount',
        'status', 'payment_status',
        'approved_by', 'approved_at', 'rejection_reason',
        'hold_until', 'confirmed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'additional_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'hold_until' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'participants' => 'integer',
    ];

    const STATUSES = [
        'draft' => ['label' => 'Draft', 'color' => 'secondary'],
        'pending' => ['label' => 'Menunggu', 'color' => 'warning'],
        'waiting_approval' => ['label' => 'Menunggu Persetujuan', 'color' => 'info'],
        'approved' => ['label' => 'Disetujui', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'danger'],
        'waiting_payment' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
        'paid' => ['label' => 'Lunas', 'color' => 'success'],
        'preparing' => ['label' => 'Persiapan', 'color' => 'info'],
        'in_delivery' => ['label' => 'Dalam Pengantaran', 'color' => 'primary'],
        'in_use' => ['label' => 'Sedang Digunakan', 'color' => 'primary'],
        'return_process' => ['label' => 'Proses Pengembalian', 'color' => 'warning'],
        'completed' => ['label' => 'Selesai', 'color' => 'success'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'danger'],
        'expired' => ['label' => 'Kadaluarsa', 'color' => 'secondary'],
    ];

    const PAYMENT_STATUSES = [
        'unpaid' => ['label' => 'Belum Bayar', 'color' => 'danger'],
        'waiting_verification' => ['label' => 'Menunggu Verifikasi', 'color' => 'warning'],
        'paid' => ['label' => 'Lunas', 'color' => 'success'],
        'partial' => ['label' => 'Bayar Sebagian', 'color' => 'warning'],
        'refunded' => ['label' => 'Dikembalikan', 'color' => 'info'],
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class)->orderBy('created_at');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class)->where('type', 'delivery')->latestOfMany();
    }

    public function bookingReturn()
    {
        return $this->hasOne(ReturnModel::class)->latestOfMany();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'expired', 'completed']);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)->where('end_date', '>=', $date);
    }

    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereNotIn('status', ['cancelled', 'expired', 'rejected']);
    }

    // Helpers
    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return self::PAYMENT_STATUSES[$this->payment_status]
            ?? ['label' => $this->payment_status, 'color' => 'secondary'];
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isExpired(): bool
    {
        return $this->hold_until && now()->gt($this->hold_until)
            && in_array($this->status, ['pending', 'waiting_payment']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'waiting_approval', 'approved', 'waiting_payment']);
    }

    // Auto generate booking number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->booking_number = 'BK/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
