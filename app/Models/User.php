<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Helpers
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['superadmin', 'admin']);
    }

    public function isBendahara(): bool
    {
        return $this->hasRole('bendahara');
    }

    public function isPetugas(): bool
    {
        return $this->hasRole('petugas');
    }

    public function isPimpinan(): bool
    {
        return $this->hasRole('pimpinan');
    }

    public function isPenyewa(): bool
    {
        return $this->hasRole('penyewa');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D6EFD&color=fff';
    }
}
