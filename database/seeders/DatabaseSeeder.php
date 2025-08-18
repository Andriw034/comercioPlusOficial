<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersWithRolesSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
