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
}
