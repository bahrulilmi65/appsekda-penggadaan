<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'code', 'name', 'type', 'nik', 'nip',
        'institution_name', 'address', 'city', 'province', 'postal_code',
        'phone', 'email', 'npwp', 'contact_person', 'contact_phone',
        'verification_status', 'rejection_reason', 'verified_at',
        'verified_by', 'notes', 'is_active',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'masyarakat' => 'Masyarakat',
        'opd' => 'OPD',
        'instansi_pemerintah' => 'Instansi Pemerintah',
        'perusahaan' => 'Perusahaan',
        'organisasi' => 'Organisasi',
        'internal' => 'Internal Sekretariat Daerah',
        'lainnya' => 'Lainnya',
    ];

    const VERIFICATION_STATUSES = [
        'unverified' => ['label' => 'Belum Diverifikasi', 'color' => 'secondary'],
        'pending' => ['label' => 'Menunggu Verifikasi', 'color' => 'warning'],
        'verified' => ['label' => 'Terverifikasi', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'danger'],
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getVerificationBadgeAttribute(): array
    {
        return self::VERIFICATION_STATUSES[$this->verification_status]
            ?? ['label' => $this->verification_status, 'color' => 'secondary'];
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    // Auto generate code
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->code)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)->count() + 1;
                $model->code = 'CST/' . $year . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
