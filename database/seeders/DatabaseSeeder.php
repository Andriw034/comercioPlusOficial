<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Solo ejecutar PermissionsSeeder si existe la tabla permissions
            // if (Schema::hasTable('permissions')) {
            //     PermissionsSeeder::class,
            // }
            CategorySeeder::class,
            StoreSeeder::class,
            ProductSeeder::class,
            // RoleSeeder::class, // si existe, puedes mantenerlo; usamos firstOrCreate en PermissionsSeeder
            // Otros seeders que ya tengas...
        ]);
    }
}
