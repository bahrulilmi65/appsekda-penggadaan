<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'code', 'name', 'slug', 'description', 'type',
        'unit', 'total_stock', 'minimum_stock', 'price', 'price_type',
        'capacity', 'location', 'floor', 'building', 'facilities_list',
        'terms_conditions', 'is_bookable', 'requires_approval', 'is_active',
        'min_booking_hours', 'max_booking_days', 'booking_advance_days', 'notes',
    ];

    protected $casts = [
        'total_stock' => 'integer',
        'minimum_stock' => 'integer',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'is_bookable' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'min_booking_hours' => 'integer',
        'max_booking_days' => 'integer',
        'booking_advance_days' => 'integer',
    ];

    const TYPES = [
        'building' => 'Gedung',
        'asset' => 'Aset',
        'equipment' => 'Peralatan',
        'consumable' => 'Konsumabel',
    ];

    const PRICE_TYPES = [
        'per_hour' => 'Per Jam',
        'per_day' => 'Per Hari',
        'per_item' => 'Per Item',
        'per_event' => 'Per Event',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(FacilityImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(FacilityImage::class)->where('is_primary', true);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBookable($query)
    {
        return $query->where('is_bookable', true)->where('is_active', true);
    }

    public function scopeBuildings($query)
    {
        return $query->where('type', 'building');
    }

    public function scopeAssets($query)
    {
        return $query->where('type', '!=', 'building');
    }

    // Helpers
    public function isBuilding(): bool
    {
        return $this->type === 'building';
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $img = $this->primaryImage ?? $this->images->first();
        if ($img) {
            return asset('storage/' . $img->file_path);
        }
        return asset('images/no-image.png');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getPriceTypeLabelAttribute(): string
    {
        return self::PRICE_TYPES[$this->price_type] ?? $this->price_type;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Auto slug & code
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
        static::created(function ($model) {
            // Create inventory record
            Inventory::create([
                'facility_id' => $model->id,
                'qty_available' => $model->total_stock,
                'qty_total' => $model->total_stock,
            ]);
        });
    }
}
