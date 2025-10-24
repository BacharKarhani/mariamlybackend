<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['name', 'shipping_price'];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get shipping price for a given address
     */
    public static function getShippingPriceForAddress($addressId)
    {
        $address = Address::with('zone')->find($addressId);
        
        if (!$address || !$address->zone) {
            return 0;
        }
        
        return $address->zone->shipping_price;
    }

    /**
     * Get shipping price for a given zone ID
     */
    public static function getShippingPriceForZone($zoneId)
    {
        $zone = self::find($zoneId);
        
        if (!$zone) {
            return 0;
        }
        
        return $zone->shipping_price;
    }
}
