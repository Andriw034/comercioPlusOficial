<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersWithRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Admin idempotente
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // cámbialo luego
            ]
        );

        // Asegurar asignación de rol admin una sola vez
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Comerciante demo (opcional)
        $merchant = User::firstOrCreate(
            ['email' => 'merchant@example.com'],
            [
                'name' => 'Merchant Demo',
                'password' => Hash::make('password'),
            ]
        );
        if (! $merchant->hasRole('comerciante')) {
            $merchant->assignRole('comerciante');
        }

        // Cliente demo (opcional)
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client Demo',
                'password' => Hash::make('password'),
            ]
        );
        if (! $client->hasRole('cliente')) {
            $client->assignRole('cliente');
        }
    }
}
                                                                                                                                                                                                                                                       