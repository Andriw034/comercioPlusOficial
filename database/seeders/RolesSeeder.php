<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Usa findOrCreate para evitar RoleAlreadyExists
        $roles = ['admin', 'comerciante', 'cliente'];

        foreach ($roles as $name) {
            Role::findOrCreate($name, 'web'); // guard 'web'
        }
    }
}
