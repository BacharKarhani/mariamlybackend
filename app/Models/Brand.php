<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'image', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // Optional helper if you want full URL for the image (like your Category)
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
