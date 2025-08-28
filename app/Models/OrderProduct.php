<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_product_id'; // Because you used order_product_id in the migration
    public $timestamps = false; // No created_at and updated_at

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity',
        'total',
    ];

    // Belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
