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
        'sku',
        'desc',
        'image',        // legacy single-image column (optional)
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
        'tags',
        'hot_offer',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_new'      => 'boolean',
        'hot_offer'   => 'boolean',
        'new_until'   => 'datetime',
        'subcategory_id' => 'integer',
        'brand_id'    => 'integer',
    ];

    // show both in JSON
    protected $appends = ['is_new_active', 'image_url', 'total_quantity', 'has_variants'];

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
        if ($this->categories->isNotEmpty() && !empty($this->categories->first()->image)) {
            return $this->categories->first()->image_url ?? url(Storage::url($this->categories->first()->image));
        }

        return null;
    }

    /**
     * Get total quantity from all variants (if product has variants) or from product quantity field
     */
    public function getTotalQuantityAttribute(): int
    {
        if ($this->has_variants) {
            return $this->variants()->sum('quantity');
        }
        return $this->quantity ?? 0;
    }

    /**
     * Check if product has variants
     */
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Check if product is in stock (considering variants)
     */
    public function isInStock(): bool
    {
        return $this->total_quantity > 0;
    }

    /**
     * Check if product is out of stock (considering variants)
     */
    public function isOutOfStock(): bool
    {
        return $this->total_quantity <= 0;
    }

    /**
     * Update product quantity field to match variant quantities (for products with variants)
     */
    public function syncQuantityFromVariants(): void
    {
        if ($this->has_variants) {
            $this->update(['quantity' => $this->variants()->sum('quantity')]);
        }
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
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
        return $this->hasMany(ProductVariant::class)->ordered();
    }

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class, 'product_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', true);
    }
}
