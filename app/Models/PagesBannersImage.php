<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PagesBannersImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'page_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the full URL for the banner image
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? url(Storage::url($this->image_path)) : null;
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific page
     */
    public function scopeForPage($query, $pageName)
    {
        return $query->where('page_name', $pageName);
    }
}
