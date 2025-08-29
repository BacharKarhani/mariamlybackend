<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image_path', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active'  => true,
        'sort_order' => 0,
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
