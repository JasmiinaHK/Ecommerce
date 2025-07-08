<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categoryIds = Category::pluck('id')->toArray();
        
        if (empty($categoryIds)) {
            $this->command->info('No categories found. Please run CategoriesTableSeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Samsung Galaxy S23',
                'description' => 'Latest Samsung smartphone with top performance and advanced camera system.',
                'price' => 999.99,
                'sale_price' => 899.99,
                'quantity' => 50,
                'sku' => 'SAMSUNG-S23-256GB',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[0] // Electronics
            ],
            [
                'name' => 'LG OLED C3 55" 4K Smart TV',
                'description' => 'Stunning 4K OLED TV with smart features and perfect blacks.',
                'price' => 1499.99,
                'quantity' => 20,
                'sku' => 'LG-OLED-C3-55',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[3] // TV & Audio
            ],
            [
                'name' => 'Samsung EcoBubble Washing Machine 8kg',
                'description' => 'Energy efficient washing machine with EcoBubble technology.',
                'price' => 699.99,
                'quantity' => 15,
                'sku' => 'SAMSUNG-WW80T554DAX',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[1] // Home Appliances
            ],
            [
                'name' => 'Samsung Galaxy Watch 5',
                'description' => 'Advanced smartwatch with health monitoring and fitness tracking.',
                'price' => 299.99,
                'sale_price' => 249.99,
                'quantity' => 30,
                'sku' => 'SAMSUNG-GW5-44MM',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[2] // Smart Watches
            ]
        ];

        foreach ($products as $productData) {
            $product = new Product($productData);
            $product->slug = Str::slug($product->name);
            $product->save();
        }
    }
}
