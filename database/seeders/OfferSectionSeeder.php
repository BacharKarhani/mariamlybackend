<?php

namespace Database\Seeders;

use App\Models\OfferSection;
use Illuminate\Database\Seeder;

class OfferSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OfferSection::create([
            'image_path' => null, // You can add an image path here if you have one
            'alt_text' => 'Valentine Offer',
            'discount_percentage' => '60% Off',
            'title' => 'Celebrate love & beauty this Valentine\'s!',
            'description' => 'Get our exclusive cosmetic Valentine gifts at a special discounted price – the perfect way to surprise someone you love.',
            'button_text' => 'Customize now',
            'button_link' => '/shop',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }
}
