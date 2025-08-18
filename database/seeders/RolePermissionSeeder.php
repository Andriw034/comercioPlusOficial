<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear permisos base de ComercioPlus
        $permisos = [
            // Productos
            'crear productos',
            'editar productos',
            'eliminar productos',
            'ver productos',

            // Tienda
            'crear tienda',
            'editar tienda',
            'ver tienda',
            'eliminar tienda',

            // Ventas y pedidos
            'ver ventas',
            'ver pedidos',
            'gestionar pedidos',

            // Administración
            'gestionar usuarios',
            'gestionar roles',
            'ver estadísticas',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }

        // 2. Crear roles
        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $comerciante = Role::firstOrCreate(['name' => 'comerciante', 'guard_name' => 'web']);
        $cliente = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'web']);
        $usuario = Role::firstOrCreate(['name' => 'usuario normal', 'guard_name' => 'web']);

        // 3. Asignar permisos a cada rol

        // Administrador: todos los permisos
        $admin->syncPermissions(Permission::all());

        // Comerciante: solo permisos de productos, pedidos y tienda
        $comerciante->syncPermissions([
            'crear productos',
            'editar productos',
            'eliminar productos',
            'ver productos',
            'crear tienda',
            'editar tienda',
            'ver tienda',
            'ver ventas',
            'ver pedidos',
            'gestionar pedidos',
        ]);

        // Cliente: solo puede ver productos, tienda y hacer pedidos
        $cliente->syncPermissions([
            'ver productos',
            'ver tienda',
            'ver pedidos',
        ]);

        // Usuario normal: sin permisos especiales
        $usuario->syncPermissions([]);

        // 4. (Opcional) Asignar roles a usuarios existentes
        $primerUsuario = User::first();
        if ($primerUsuario && !$primerUsuario->hasRole('administrador')) {
            $primerUsuario->assignRole('administrador');
        }
    }
}
