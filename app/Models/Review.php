<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'comment',
        'stars_count',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'stars_count' => 'integer'
    ];

    protected $appends = ['user_name'];

    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
