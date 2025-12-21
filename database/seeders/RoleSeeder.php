<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Insertar rol "usuario normal" con id=2 si no existe
        $exists = DB::table('roles')->where('id', 2)->exists();
        if (!$exists) {
            DB::table('roles')->insert([
                'id' => 2,
                'name' => 'usuario normal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
