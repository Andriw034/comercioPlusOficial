<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswords extends Command
{
    protected $signature = 'users:update-passwords';
    protected $description = 'Actualizar contraseÃ±as antiguas no hasheadas a bcrypt';

    public function handle()
    {
        $users = User::all();
        $updatedCount = 0;

        foreach ($users as $user) {
            $password = $user->password;

            // Verificar si la contraseÃ±a parece no estar hasheada (no empieza con $2y$ o $2a$)
            if (!str_starts_with($password, '$2y$') && !str_starts_with($password, '$2a$')) {
                $user->password = Hash::make($password);
                $user->save();
                $updatedCount++;
                $this->info("ContraseÃ±a actualizada para usuario ID: {$user->id}");
            }
        }

        $this->info("Proceso completado. Total de contraseÃ±as actualizadas: {$updatedCount}");
        return 0;
    }
}
