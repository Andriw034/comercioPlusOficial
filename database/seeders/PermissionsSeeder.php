<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $sellerRole = Role::create(['name' => 'seller']);

        // Crear permisos
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage categories']);
        Permission::create(['name' => 'view dashboard']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo(['manage products', 'manage categories', 'view dashboard']);
        $sellerRole->givePermissionTo(['manage products', 'view dashboard']);
    }
}
