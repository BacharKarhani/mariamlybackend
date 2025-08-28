<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id'; // Important because you use order_id not id
    public $timestamps = false; // You manually control date_added, date_modified

    protected $fillable = [
        'user_id',
        'address_id',
        'subtotal',
        'shipping',
        'total',
        'payment_code',
        'logistic',
        'track',
        'order_status',
        'date_added',
        'date_modified',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'order_id');
    }
}
