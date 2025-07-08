<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Smartphones, laptops, tablets and other electronics'],
            ['name' => 'Home Appliances', 'description' => 'Home appliances and white goods'],
            ['name' => 'Smart Watches', 'description' => 'Smart watches and wearables'],
            ['name' => 'TV & Audio', 'description' => 'Televisions and audio equipment']
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true
            ]);
        }
    }
}
