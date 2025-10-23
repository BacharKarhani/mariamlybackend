<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'hex_color',
        'sku',
        'quantity',
        'sort_order',
        'buying_price',
        'regular_price',
        'discount',
        'selling_price',
        'weight',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * Get the RGB values from hex color
     */
    public function getRgbColorAttribute()
    {
        if (!$this->hex_color) {
            return null;
        }
        
        $hex = ltrim($this->hex_color, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Get the CSS color value (with fallback)
     */
    public function getCssColorAttribute()
    {
        return $this->hex_color ?: '#CCCCCC'; // Default gray if no color
    }

    /**
     * Scope to order variants by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope for variants with stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for out of stock variants
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock()
    {
        return $this->quantity > 0;
    }

    /**
     * Check if variant is out of stock
     */
    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    /**
     * Get the effective selling price for this variant
     * Falls back to product price if variant doesn't have its own pricing
     */
    public function getEffectiveSellingPriceAttribute()
    {
        return $this->selling_price ?? $this->product->selling_price;
    }

    /**
     * Get the effective regular price for this variant
     * Falls back to product price if variant doesn't have its own pricing
     */
    public function getEffectiveRegularPriceAttribute()
    {
        return $this->regular_price ?? $this->product->regular_price;
    }

    /**
     * Get the effective discount for this variant
     * Falls back to product discount if variant doesn't have its own pricing
     */
    public function getEffectiveDiscountAttribute()
    {
        return $this->discount ?? $this->product->discount;
    }

    /**
     * Get the effective buying price for this variant
     * Falls back to product price if variant doesn't have its own pricing
     */
    public function getEffectiveBuyingPriceAttribute()
    {
        return $this->buying_price ?? $this->product->buying_price;
    }

    /**
     * Check if this variant has its own pricing (different from product)
     */
    public function hasCustomPricing()
    {
        return !is_null($this->selling_price) || !is_null($this->regular_price) || !is_null($this->buying_price);
    }

    /**
     * Calculate selling price based on regular price and discount
     */
    public function calculateSellingPrice()
    {
        if ($this->regular_price && $this->discount) {
            return $this->regular_price - ($this->regular_price * $this->discount / 100);
        }
        return $this->regular_price ?? $this->selling_price;
    }
}
