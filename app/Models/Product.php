<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'image',
        'category_id',
        'buying_price',
        'selling_price',
        'quantity',
        'is_trending',
        'regular_price',
        'discount',
        'is_new',      // NEW
        'new_until',   // NEW
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_new'      => 'boolean',
        'new_until'   => 'date',
    ];

    // يظهر تلقائياً في JSON
    protected $appends = ['is_new_active'];

    public function getIsNewActiveAttribute(): bool
    {
        if (! $this->is_new) return false;
        if (is_null($this->new_until)) return true;
        return now()->startOfDay()->lte($this->new_until);
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

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class, 'product_id', 'id');
    }
}
