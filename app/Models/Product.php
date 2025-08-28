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
    ];

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
