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
        DB::table('categories')->insert([
            ['name' => 'Tecnología', 'slug' => 'tecnologia'],
            ['name' => 'Hogar', 'slug' => 'hogar'],
            ['name' => 'Moda', 'slug' => 'moda'],
            ['name' => 'Deportes', 'slug' => 'deportes'],
            ['name' => 'Libros', 'slug' => 'libros'],
        ]);
    }
}
