<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing categories or create some if they don't exist
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $categories = collect([
                Category::create(['name' => 'Electronics']),
                Category::create(['name' => 'Clothing']),
                Category::create(['name' => 'Home & Garden']),
            ]);
        }

        $subcategories = [
            // Electronics subcategories
            ['name' => 'Smartphones', 'category_id' => $categories->where('name', 'Electronics')->first()?->id],
            ['name' => 'Laptops', 'category_id' => $categories->where('name', 'Electronics')->first()?->id],
            ['name' => 'Tablets', 'category_id' => $categories->where('name', 'Electronics')->first()?->id],
            ['name' => 'Accessories', 'category_id' => $categories->where('name', 'Electronics')->first()?->id],
            
            // Clothing subcategories
            ['name' => 'Men\'s Clothing', 'category_id' => $categories->where('name', 'Clothing')->first()?->id],
            ['name' => 'Women\'s Clothing', 'category_id' => $categories->where('name', 'Clothing')->first()?->id],
            ['name' => 'Kids\' Clothing', 'category_id' => $categories->where('name', 'Clothing')->first()?->id],
            ['name' => 'Shoes', 'category_id' => $categories->where('name', 'Clothing')->first()?->id],
            
            // Home & Garden subcategories
            ['name' => 'Furniture', 'category_id' => $categories->where('name', 'Home & Garden')->first()?->id],
            ['name' => 'Kitchen', 'category_id' => $categories->where('name', 'Home & Garden')->first()?->id],
            ['name' => 'Garden Tools', 'category_id' => $categories->where('name', 'Home & Garden')->first()?->id],
            ['name' => 'Decor', 'category_id' => $categories->where('name', 'Home & Garden')->first()?->id],
        ];

        foreach ($subcategories as $subcategoryData) {
            if ($subcategoryData['category_id']) {
                Subcategory::create($subcategoryData);
            }
        }
    }
}
