<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number', 'booking_id', 'customer_id',
        'amount_due', 'amount_paid', 'payment_method',
        'bank_name', 'account_number', 'account_name',
        'transaction_number', 'payment_date', 'proof_of_payment',
        'status', 'notes', 'rejection_reason',
        'verified_by', 'verified_at', 'due_date',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'due_date' => 'datetime',
        'verified_at' => 'datetime',
    ];

    const STATUSES = [
        'unpaid' => ['label' => 'Belum Bayar', 'color' => 'danger'],
        'waiting_verification' => ['label' => 'Menunggu Verifikasi', 'color' => 'warning'],
        'paid' => ['label' => 'Lunas', 'color' => 'success'],
        'partial' => ['label' => 'Bayar Sebagian', 'color' => 'info'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'danger'],
        'refunded' => ['label' => 'Dikembalikan', 'color' => 'secondary'],
    ];

    const PAYMENT_METHODS = [
        'transfer' => 'Transfer Bank',
        'cash' => 'Tunai',
        'virtual_account' => 'Virtual Account',
        'qris' => 'QRIS',
        'lainnya' => 'Lainnya',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_of_payment ? asset('storage/' . $this->proof_of_payment) : null;
    }

    public function getFormattedAmountDueAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_due, 0, ',', '.');
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_paid, 0, ',', '.');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->payment_number)) {
                $year = date('Y');
                $month = date('m');
                $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $model->payment_number = 'PAY/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
