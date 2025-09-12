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

    // Helper methods for date formatting
    public function getFormattedDateAttribute()
    {
        return $this->date_added->format('M d, Y');
    }

    public function getMonthYearAttribute()
    {
        return $this->date_added->format('Y-m');
    }

    public function getMonthNameAttribute()
    {
        return $this->date_added->format('F Y');
    }

    // Scope for filtering by month
    public function scopeForMonth($query, $year, $month)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        
        return $query->whereBetween('date_added', [$startDate, $endDate]);
    }

    // Scope for filtering by year
    public function scopeForYear($query, $year)
    {
        $startDate = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
        $endDate = \Carbon\Carbon::create($year, 12, 31)->endOfYear();
        
        return $query->whereBetween('date_added', [$startDate, $endDate]);
    }

    // Scope for recent orders (last N months)
    public function scopeRecent($query, $months = 6)
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        
        return $query->where('date_added', '>=', $startDate);
    }
}
