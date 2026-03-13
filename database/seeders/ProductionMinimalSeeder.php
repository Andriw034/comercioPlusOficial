<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class ProductionMinimalSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $now = now();

        $roles = [
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'comerciante', 'guard_name' => 'web'],
            ['name' => 'cliente', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $permissions = [
            'manage products',
            'manage categories',
            'manage stores',
            'manage orders',
            'manage users',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $adminRoleId = DB::table('roles')->where('name', 'admin')->where('guard_name', 'web')->value('id');
        $merchantRoleId = DB::table('roles')->where('name', 'comerciante')->where('guard_name', 'web')->value('id');
        $clientRoleId = DB::table('roles')->where('name', 'cliente')->where('guard_name', 'web')->value('id');

        $permissionIds = DB::table('permissions')->pluck('id', 'name');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['permission_id' => $permissionId, 'role_id' => $adminRoleId],
                ['permission_id' => $permissionId, 'role_id' => $adminRoleId]
            );
        }

        foreach (['manage products', 'manage categories', 'manage stores', 'manage orders', 'view dashboard'] as $permissionName) {
            if (!isset($permissionIds[$permissionName])) {
                continue;
            }
            DB::table('role_has_permissions')->updateOrInsert(
                ['permission_id' => $permissionIds[$permissionName], 'role_id' => $merchantRoleId],
                ['permission_id' => $permissionIds[$permissionName], 'role_id' => $merchantRoleId]
            );
        }

        if (isset($permissionIds['view dashboard'])) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['permission_id' => $permissionIds['view dashboard'], 'role_id' => $clientRoleId],
                ['permission_id' => $permissionIds['view dashboard'], 'role_id' => $clientRoleId]
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@comercioplus.local'],
            [
                'name' => 'Administrador ComercioPlus',
                'password' => Hash::make('Admin12345!'),
                'role' => 'merchant',
                'status' => true,
                'email_verified_at' => now(),
            ]
        );

        DB::table('model_has_roles')->updateOrInsert(
            [
                'role_id' => $adminRoleId,
                'model_type' => User::class,
                'model_id' => $admin->id,
            ],
            [
                'role_id' => $adminRoleId,
                'model_type' => User::class,
                'model_id' => $admin->id,
            ]
        );

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
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('ProductionMinimalSeeder ejecutado: roles, permisos, admin y categorías base.');
    }
}
