<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'image',        // legacy single-image column (optional)
        'category_id',
        'subcategory_id',
        'brand_id',
        'buying_price',
        'selling_price',
        'quantity',
        'weight',
        'ingredients',
        'usage_instructions',
        'is_trending',
        'regular_price',
        'discount',
        'is_new',
        'new_until',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_new'      => 'boolean',
        'new_until'   => 'datetime',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'brand_id'    => 'integer',
    ];

    // show both in JSON
    protected $appends = ['is_new_active', 'image_url'];

    public function getIsNewActiveAttribute(): bool
    {
        if (!$this->is_new) return false;
        if (is_null($this->new_until)) return true;
        return now()->startOfDay()->lte($this->new_until);
    }

    // NEW: a guaranteed product image URL (with sensible fallbacks)
    public function getImageUrlAttribute()
    {
        // 1) first variant's first image (has full URL via ProductImage::getUrlAttribute)
        if ($firstVariant = $this->variants->first()) {
            if ($firstImage = $firstVariant->images->first()) {
                return $firstImage->url;
            }
        }

        // 2) legacy single image column if present
        if (!empty($this->image)) {
            return url(Storage::url($this->image));
        }

        // 3) optional fallbacks so cards don't look empty
        if ($this->brand && !empty($this->brand->image)) {
            // if Brand has its own image_url accessor, that will already be present
            return $this->brand->image_url ?? url(Storage::url($this->brand->image));
        }
        if ($this->category && !empty($this->category->image)) {
            return $this->category->image_url ?? url(Storage::url($this->category->image));
        }

        return null;
    }

    // Scope للمنتجات الجديدة الفعّالة
    public function scopeNewActive($query)
    {
        return $query->where('is_new', true)
            ->where(function ($q) {
                $q->whereNull('new_until')
                  ->orWhereDate('new_until', '>=', now()->toDateString());
            });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Note: Images are now handled through variants, not directly on products
    // Use $product->variants->pluck('images')->flatten() to get all images
    /*
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    */

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class, 'product_id', 'id');
    }
}
