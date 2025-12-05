<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar todos los registros de usuarios';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Deshabilitar temporalmente las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar registros de la tabla users
        DB::table('users')->truncate();

        // Habilitar nuevamente las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Todos los usuarios han sido eliminados correctamente.');
        return 0;
    }
}
