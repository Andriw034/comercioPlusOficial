<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'comerciante', 'cliente'] as $r) {
            Role::findOrCreate($r, 'web');
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@comercio.plus'],
            ['name' => 'Admin ComercioPlus', 'password' => Hash::make('admin12345'), 'status' => 1]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $merchant = User::firstOrCreate(
            ['email' => 'merchant@comercio.plus'],
            ['name' => 'Comerciante Demo', 'password' => Hash::make('merchant123'), 'status' => 1]
        );
        if (! $merchant->hasRole('comerciante')) {
            $merchant->assignRole('comerciante');
        }

        // Asignar 'cliente' a usuarios que no tengan rol
        User::whereDoesntHave('roles')->get()->each(fn($u) => $u->assignRole('cliente'));
    }
}


