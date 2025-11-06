<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Producto 1',
            'slug' => 'producto-1',
            'description' => 'Descripción del producto 1',
            'price' => 19.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);

        Product::create([
            'name' => 'Producto 2',
            'slug' => 'producto-2',
            'description' => 'Descripción del producto 2',
            'price' => 29.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);

        Product::create([
            'name' => 'Producto 3',
            'slug' => 'producto-3',
            'description' => 'Descripción del producto 3',
            'price' => 39.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);

        Product::create([
            'name' => 'Producto 4',
            'slug' => 'producto-4',
            'description' => 'Descripción del producto 4',
            'price' => 49.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);

        Product::create([
            'name' => 'Producto 5',
            'slug' => 'producto-5',
            'description' => 'Descripción del producto 5',
            'price' => 59.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);

        Product::create([
            'name' => 'Producto 6',
            'slug' => 'producto-6',
            'description' => 'Descripción del producto 6',
            'price' => 69.99,
            'image' => 'https://via.placeholder.com/300x200'
        ]);
    }
}