<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDocument extends Model
{
    protected $fillable = [
        'customer_id', 'document_type', 'document_name',
        'file_path', 'original_name', 'file_size', 'mime_type',
        'is_verified', 'notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
