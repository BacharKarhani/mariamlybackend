<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['variant_id', 'path'];

    // Make "url" appear in JSON
    protected $appends = ['url'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product()
    {
        return $this->hasOneThrough(Product::class, ProductVariant::class, 'id', 'id', 'variant_id', 'product_id');
    }

    public function getUrlAttribute()
    {
        return $this->path ? url(Storage::url($this->path)) : null;
    }
}
