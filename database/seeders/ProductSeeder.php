<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Obtener un usuario para asignar a los productos
        $user = User::first();

        // Obtener categorías existentes
        $categories = Category::all();

        if ($user && $categories->count() > 0) {
            foreach ($categories as $category) {
                Product::create([
                    'name' => 'Producto de ejemplo para ' . $category->name,
                    'description' => 'Descripción detallada del producto para la categoría ' . $category->name,
                    'price' => rand(1000, 10000) / 100,
                    'stock' => rand(1, 100),
                    'image' => null,
                    'category_id' => $category->id,
                    'offer' => (bool) rand(0, 1),
                    'average_rating' => rand(0, 50) / 10,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
