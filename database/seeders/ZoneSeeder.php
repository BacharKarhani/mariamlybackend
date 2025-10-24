<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Zone;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Beirut',
                'shipping_price' => 2.00,
            ],
            [
                'name' => 'Mount Lebanon',
                'shipping_price' => 3.00,
            ],
            [
                'name' => 'North Lebanon',
                'shipping_price' => 4.00,
            ],
            [
                'name' => 'South Lebanon',
                'shipping_price' => 4.00,
            ],
            [
                'name' => 'Bekaa',
                'shipping_price' => 5.00,
            ],
            [
                'name' => 'Nabatieh',
                'shipping_price' => 5.00,
            ],
            [
                'name' => 'Akkar',
                'shipping_price' => 6.00,
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}