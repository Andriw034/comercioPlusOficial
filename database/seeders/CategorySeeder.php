<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Main categories
        $motos = Category::create(['name' => 'Motos']);
        $accesorios = Category::create(['name' => 'Accesorios']);

        // Brands as subcategories under Motos
        $yamaha = Category::create(['name' => 'Yamaha', 'parent_id' => $motos->id]);
        $honda = Category::create(['name' => 'Honda', 'parent_id' => $motos->id]);
        $suzuki = Category::create(['name' => 'Suzuki', 'parent_id' => $motos->id]);
        $bajaj = Category::create(['name' => 'Bajaj', 'parent_id' => $motos->id]);
        $akt = Category::create(['name' => 'AKT', 'parent_id' => $motos->id]);

        // Parts as sub-subcategories under each brand
        $parts = ['Empaques', 'Bandas', 'Pastillas', 'Guayas'];

        foreach ([$yamaha, $honda, $suzuki, $bajaj, $akt] as $brand) {
            foreach ($parts as $part) {
                Category::create([
                    'name' => $part,
                    'parent_id' => $brand->id,
                ]);
            }
        }

        // Accessories subcategories
        Category::create(['name' => 'Cascos', 'parent_id' => $accesorios->id]);
        Category::create(['name' => 'Llantas', 'parent_id' => $accesorios->id]);
        Category::create(['name' => 'Guantes', 'parent_id' => $accesorios->id]);
    }
}
