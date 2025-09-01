<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear usuarios de ejemplo con diferentes roles
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@comercioreal.com',
                'password' => Hash::make('your_admin_password'),
                'role' => 'super-admin'
            ],
            [
                'name' => 'Comerciante Ejemplo',
                'email' => 'comerciante@comercioreal.com',
                'password' => Hash::make('your_merchant_password'),
                'role' => 'comerciante'
            ],
            [
                'name' => 'Cliente Ejemplo',
                'email' => 'cliente@comercioreal.com',
                'password' => Hash::make('your_customer_password'),
                'role' => 'cliente'
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Usuarios de ejemplo creados exitosamente');
        $this->command->info('Credenciales:');
        $this->command->info('Admin: admin@comercioreal.com');
        $this->command->info('Comerciante: comerciante@comercioreal.com');
        $this->command->info('Cliente: cliente@comercioreal.com');
        $this->command->warn('IMPORTANTE: Cambie las contraseñas por defecto inmediatamente después de la instalación');
    }
}
