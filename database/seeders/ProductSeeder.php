<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Asegurar dependencias mínimas (usuario, categorías, tienda demo)
        $userId = User::value('id');
        if (!$userId) {
            $user = User::query()->create([
                'name' => 'Demo Comercioplus',
                'email' => 'demo@comercioplus.test',
                'password' => bcrypt('password'),
            ]);
            $userId = $user->id;
        }

        $catMotos = Category::firstOrCreate(['id' => 1], ['name' => 'Motos', 'slug' => 'motos']);
        $catLlantas = Category::firstOrCreate(['id' => 2], ['name' => 'Llantas', 'slug' => 'llantas']);

        $store = Store::firstOrCreate(['id' => 1], [
            'name'        => 'Tienda Demo',
            'slug'        => 'tienda-demo',
            'description' => 'Tienda de prueba para seeders.',
            'logo'        => null,
            'cover'       => null,
            'user_id'     => $userId,
        ]);

        // 2) Limpiar products
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3) Helper para slug único
        $uniqueSlug = function (string $name): string {
            $base = Str::slug($name);
            $slug = $base;
            $k = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$k}";
                $k++;
            }
            return $slug;
        };

        // 4) Productos demo
        $items = [
            ['name' => 'Producto de ejemplo para Motos', 'category_id' => $catMotos->id],
            ['name' => 'Casco integral Pro',            'category_id' => $catMotos->id],
            ['name' => 'Llanta 90/90 R17 Sport',        'category_id' => $catLlantas->id],
            ['name' => 'Guantes Racing XL',             'category_id' => $catMotos->id],
            ['name' => 'Aceite 10W-40 Sintético',       'category_id' => $catMotos->id],
            ['name' => 'Kit de frenos delantero',       'category_id' => $catMotos->id],
            ['name' => 'Batería de litio 12V',          'category_id' => $catMotos->id],
            ['name' => 'Espejos retrovisores cromados', 'category_id' => $catMotos->id],
        ];

        foreach ($items as $i => $data) {
            $name = $data['name'];
            $slug = $uniqueSlug($name);

            Product::create([
                'name'            => $name,
                'slug'            => $slug,
                'description'     => 'Descripción detallada del producto de prueba para la tienda ComercioPlus.',
                'price'           => fake()->randomFloat(2, 10, 300),
                'stock'           => fake()->numberBetween(5, 200),
                // Imagen aleatoria desde picsum.photos
                'image'           => "https://picsum.photos/seed/product{$i}/600/400",
                'category_id'     => $data['category_id'],
                'store_id'        => $store->id,
                'offer'           => fake()->boolean(40) ? 1 : 0,
                'average_rating'  => fake()->randomFloat(1, 3, 5),
                'user_id'         => $userId,
            ]);
        }
    }
}
