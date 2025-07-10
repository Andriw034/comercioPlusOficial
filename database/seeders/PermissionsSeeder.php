<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'products.view','products.create','products.update','products.delete',
            'categories.view','categories.create','categories.update','categories.delete',
            'stores.view','stores.create','stores.update','stores.delete',
            'orders.view','orders.create','orders.update','orders.delete',
            'carts.view','carts.create','carts.update','carts.delete',
            'users.view','users.create','users.update','users.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Map de roles EN/ES → mismo set de permisos
        $roleMap = [
            // admin
            'admin' => $permissions,
            'administrador' => $permissions,

            // seller / comerciante
            'seller' => [
                'products.view','products.create','products.update',
                'categories.view',
                'orders.view','orders.update',
                'carts.view','carts.update',
                'stores.view','stores.update',
            ],
            'comerciante' => [
                'products.view','products.create','products.update',
                'categories.view',
                'orders.view','orders.update',
                'carts.view','carts.update',
                'stores.view','stores.update',
            ],

            // customer / cliente
            'customer' => [
                'products.view','categories.view','stores.view',
                'orders.view','orders.create',
                'carts.view','carts.create','carts.update',
            ],
            'cliente' => [
                'products.view','categories.view','stores.view',
                'orders.view','orders.create',
                'carts.view','carts.create','carts.update',
            ],
        ];

        foreach ($roleMap as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
