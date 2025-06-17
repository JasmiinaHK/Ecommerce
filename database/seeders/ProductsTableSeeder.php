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
                'name' => 'Pametni telefon Samsung Galaxy S21',
                'description' => 'Najnoviji pametni telefon sa najboljim performansama.',
                'price' => 999.99,
                'sale_price' => 899.99,
                'quantity' => 50,
                'sku' => 'SAMSUNG-S21-001',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[0] // Elektronika
            ],
            [
                'name' => 'LG Smart TV 55" 4K',
                'description' => 'Pametni TV sa 4K rezolucijom i smart funkcijama.',
                'price' => 1299.99,
                'quantity' => 30,
                'sku' => 'LG-TV-55-4K',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[0] // Elektronika
            ],
            [
                'name' => 'Mašina za veš Beko 8kg',
                'description' => 'Mašina za veš sa 8kg kapaciteta i energetskom efikasnošću A+++',
                'price' => 599.99,
                'quantity' => 25,
                'sku' => 'BEKO-WM-8KG',
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categoryIds[1] // Kućni aparati
            ],
            [
                'name' => 'Patike Nike Air Max',
                'description' => 'Sportske patike za udobnost tokom čitavog dana.',
                'price' => 129.99,
                'sale_price' => 99.99,
                'quantity' => 100,
                'sku' => 'NIKE-AIR-MAX-42',
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categoryIds[4] // Sportska oprema
            ],
            [
                'name' => 'Krema za lice Nivea Q10',
                'description' => 'Njegujuća krema za lice sa koenzimom Q10.',
                'price' => 14.99,
                'quantity' => 200,
                'sku' => 'NIVEA-Q10-50ML',
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categoryIds[3] // Lijekovi i kozmetika
            ]
        ];

        foreach ($products as $productData) {
            $product = new Product($productData);
            $product->slug = Str::slug($product->name);
            $product->save();
        }
    }
}
