<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name' => 'Neumáticos',
                'slug' => Str::slug('Neumáticos'),
                'short_description' => 'Llantas y cubiertas para todo tipo de vehículos.',
                'sales_count' => 1250,
                'popularity' => 980,
                'is_popular' => true,
            ],
            [
                'name' => 'Baterías',
                'slug' => Str::slug('Baterías'),
                'short_description' => 'Baterías de alto rendimiento para autos y motos.',
                'sales_count' => 980,
                'popularity' => 870,
                'is_popular' => true,
            ],
            [
                'name' => 'Filtros de aceite',
                'slug' => Str::slug('Filtros de aceite'),
                'short_description' => 'Filtros de aceite para prolongar la vida del motor.',
                'sales_count' => 800,
                'popularity' => 750,
                'is_popular' => true,
            ],
            [
                'name' => 'Pastillas de freno',
                'slug' => Str::slug('Pastillas de freno'),
                'short_description' => 'Sistema de frenos seguro y confiable.',
                'sales_count' => 920,
                'popularity' => 890,
                'is_popular' => true,
            ],
            [
                'name' => 'Aceites y lubricantes',
                'slug' => Str::slug('Aceites y lubricantes'),
                'short_description' => 'Lubricantes de motor de alta calidad.',
                'sales_count' => 1100,
                'popularity' => 940,
                'is_popular' => true,
            ],
        ];

        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('categories')->insert($categories);
    }
}
