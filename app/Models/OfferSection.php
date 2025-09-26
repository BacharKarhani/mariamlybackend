<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferSection extends Model
{
    protected $fillable = [
        'image_path', 
        'alt_text', 
        'discount_percentage', 
        'title', 
        'description', 
        'button_text', 
        'button_link', 
        'is_active', 
        'sort_order'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active'  => true,
        'sort_order' => 0,
        'alt_text'   => 'Offer Image',
        'button_text' => 'Customize now',
        'button_link' => '/shop',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : '';
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
