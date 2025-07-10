<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run()
    {
        // Crear algunos roles básicos si no existen
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $comercianteRole = Role::firstOrCreate(['name' => 'comerciante']);
        
        // Crear algunos usuarios comerciantes si no existen
        $merchant1 = User::firstOrCreate(
            ['email' => 'comerciante1@ejemplo.com'],
            [
                'name' => 'Juan Pérez',
                'password' => bcrypt('password'),
                'role_id' => $sellerRole->id,
            ]
        );

        $merchant2 = User::firstOrCreate(
            ['email' => 'comerciante2@ejemplo.com'],
            [
                'name' => 'María García',
                'password' => bcrypt('password'),
                'role_id' => $comercianteRole->id,
            ]
        );

        // Crear tiendas para estos comerciantes
        $merchants = [$merchant1, $merchant2];

        foreach ($merchants as $merchant) {
            Store::firstOrCreate(
                ['user_id' => $merchant->id],
                [
                    'name' => 'Tienda de ' . $merchant->name,
                    'slug' => Str::slug('Tienda de ' . $merchant->name),
                    'description' => 'Descripción de la tienda de ' . $merchant->name,
                    'direccion' => 'Dirección de ejemplo para ' . $merchant->name,
                    'categoria_principal' => 'Repuestos de Motos',
                    'theme' => 'default',
                ]
            );
        }
    }
}
