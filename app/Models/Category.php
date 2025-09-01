<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image'];

    protected $appends = ['image_url']; // add this

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }
}

