<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Cascos y protección', 'slug' => 'cascos-y-proteccion', 'description' => 'Cascos, guantes, chaquetas y seguridad'],
            ['name' => 'Accesorios para moto', 'slug' => 'accesorios-para-moto', 'description' => 'Accesorios y mejoras para tu moto'],
            ['name' => 'Frenos y suspensión', 'slug' => 'frenos-y-suspension', 'description' => 'Pastillas, discos y suspensión'],
            ['name' => 'Llantas y rines', 'slug' => 'llantas-y-rines', 'description' => 'Llantas, rines y neumáticos'],
            ['name' => 'Lubricantes y mantenimiento', 'slug' => 'lubricantes-y-mantenimiento', 'description' => 'Aceites y mantenimiento'],
            ['name' => 'Repuestos generales', 'slug' => 'repuestos-generales', 'description' => 'Repuestos para todo tipo de moto'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'description' => $category['description']]
            );
        }
    }
}
