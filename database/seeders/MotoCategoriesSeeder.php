<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Store;

class MotoCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Ajusta el criterio para escoger la tienda del usuario actual.
        // Para desarrollo: toma la primera tienda o la tienda con id 12 (según tu log).
        $store = Store::query()->first() ?? Store::query()->find(12);
        if (!$store) return;

        $nombres = [
            'Cascos',
            'Guantes',
            'Chaquetas',
            'Botas',
            'Repuestos',
            'Accesorios'
        ];

        foreach ($nombres as $n) {
            Category::firstOrCreate([
                'name' => $n,
                'store_id' => $store->id,
            ]);
        }
    }
}
