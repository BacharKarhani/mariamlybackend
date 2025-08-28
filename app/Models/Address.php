<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone_number',
        'zone_id',
        'full_address',
        'more_details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
{
    return $this->belongsTo(Zone::class);
}

}
