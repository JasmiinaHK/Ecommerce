<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create categories
        $categories = [
            ['name' => 'Računala i oprema', 'slug' => 'racunala-i-oprema', 'description' => 'Prijenosna i stolna računala, komponente i oprema'],
            ['name' => 'Mobiteli i tableti', 'slug' => 'mobiteli-i-tableti', 'description' => 'Pametni telefoni, tableti i pametni satovi'],
            ['name' => 'TV i audio', 'slug' => 'tv-i-audio', 'description' => 'Televizori, zvučnici i audio oprema'],
            ['name' => 'Kućanski aparati', 'slug' => 'kucanski-aparati', 'description' => 'Bijela tehnika i mali kućanski aparati'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'description' => 'Video igre, konzole i gaming oprema'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Sample products
        $products = [
            [
                'name' => 'LENOVO IdeaPad 5 Pro 16ACH6',
                'slug' => 'lenovo-ideapad-5-pro-16ach6',
                'description' => '16" WQXGA (2560x1600) IPS 350nits, Ryzen 7 5800H, 16GB, 512GB SSD, Win 11',
                'price' => 7999.99,
                'sale_price' => 7499.99,
                'sku' => 'LP5P-16ACH6',
                'quantity' => 15,
                'is_active' => true,
                'is_featured' => true,
                'category_id' => 1,
            ],
            [
                'name' => 'SAMSUNG Galaxy S23 Ultra 5G 256GB',
                'slug' => 'samsung-galaxy-s23-ultra-5g-256gb',
                'description' => '6.8" Dynamic AMOLED 2X, 12GB RAM, 256GB, 200MP + 12MP + 10MP + 10MP, 5000mAh, crna',
                'price' => 10999.99,
                'sale_price' => 9999.99,
                'sku' => 'SGS23U-256',
                'quantity' => 10,
                'is_active' => true,
                'is_featured' => true,
                'category_id' => 2,
            ],
            [
                'name' => 'LG OLED C3 65" 4K HDR',
                'slug' => 'lg-oled-c3-65-4k-hdr',
                'description' => '65" OLED evo, 4K HDR, webOS, HDMI 2.1, Dolby Vision IQ, Dolby Atmos, Magic Remote',
                'price' => 14999.99,
                'sku' => 'OLED65C34LA',
                'quantity' => 5,
                'is_active' => true,
                'is_featured' => true,
                'category_id' => 3,
            ],
            [
                'name' => 'GORENJE K 5231 WG',
                'slug' => 'gorenje-k-5231-wg',
                'description' => 'Plinovodni štednjak, 4 plamenika, staklokeramička ploča, crna',
                'price' => 2499.99,
                'sku' => 'K5231WG',
                'quantity' => 8,
                'is_active' => true,
                'category_id' => 4,
            ],
            [
                'name' => 'PlayStation 5 Digital Edition',
                'slug' => 'playstation-5-digital-edition',
                'description' => 'Sony PlayStation 5 Digital Edition, 825GB SSD, DualSense kontroler, bijela',
                'price' => 5499.99,
                'sale_price' => 4999.99,
                'sku' => 'PS5-DIGITAL',
                'quantity' => 12,
                'is_active' => true,
                'is_featured' => true,
                'category_id' => 5,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Add a primary image for each product
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'products/placeholder.jpg',
                'is_primary' => true,
            ]);
        }
    }
}
