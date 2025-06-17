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
            ['name' => 'Elektronika', 'description' => 'Pametni telefoni, laptopovi, tablet računari i ostala elektronika'],
            ['name' => 'Kućni aparati', 'description' => 'Bela tehnika i mali kućni aparati'],
            ['name' => 'Moda', 'description' => 'Odjeća, obuća i modni dodaci'],
            ['name' => 'Lijekovi i kozmetika', 'description' => 'Lijekovi, kozmetika i njega lica i tijela'],
            ['name' => 'Sportska oprema', 'description' => 'Oprema za razne sportske aktivnosti'],
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
