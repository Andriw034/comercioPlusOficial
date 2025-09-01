<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ResetAccountsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin','comerciante','cliente'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $emails = [
            'admin@comercioreal.com',
            'comerciante@comercioreal.com',
            'cliente@comercioreal.com',
        ];

        // limpia tokens y roles SOLO de esas cuentas
        DB::table('personal_access_tokens')->whereIn('tokenable_id', function ($q) use ($emails) {
            $q->select('id')->from('users')->whereIn('email', $emails);
        })->where('tokenable_type', User::class)->delete();

        DB::table('model_has_roles')->whereIn('model_id', function ($q) use ($emails) {
            $q->select('id')->from('users')->whereIn('email', $emails);
        })->where('model_type', User::class)->delete();

        DB::table('model_has_permissions')->whereIn('model_id', function ($q) use ($emails) {
            $q->select('id')->from('users')->whereIn('email', $emails);
        })->where('model_type', User::class)->delete();

        // borra SOLO esas cuentas
        User::whereIn('email', $emails)->delete();

        // las recrea
        $admin = User::create([
            'name' => 'Super Admin', 'email' => 'admin@comercioreal.com',
            'password' => Hash::make('your_admin_password'), 'status' => true,
        ]);
        $admin->assignRole('admin');

        $merchant = User::create([
            'name' => 'Comerciante Demo', 'email' => 'comerciante@comercioreal.com',
            'password' => Hash::make('your_merchant_password'), 'status' => true,
        ]);
        $merchant->assignRole('comerciante');

        $client = User::create([
            'name' => 'Cliente Demo', 'email' => 'cliente@comercioreal.com',
            'password' => Hash::make('your_customer_password'), 'status' => true,
        ]);
        $client->assignRole('cliente');
    }
}
