<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckNewStoreColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:new-store-columns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the new columns have been added to the stores table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get the table structure
            $columns = DB::select('SHOW COLUMNS FROM stores');
            
            // Columns that should be in the stores table
            $expectedColumns = [
    'id',
    'user_id',
    'name',
    'slug',
    'logo',                // <-- en tu BD actual es 'logo' (no 'logo_path')
    // 'cover_path',       // si no existe en tu BD, quítalo
    'primary_color',
    'descripcion',         // <-- usas 'descripcion' (no 'description')
    'direccion',
    'telefono',
    'estado',
    'horario_atencion',
    'categoria_principal',
    'calificacion_promedio',
    'created_at',
    'updated_at',
    // opcionales que tienes: 'background_color','text_color','button_color'
];
            
            $this->info('Verificando las columnas de la tabla stores...');
            $this->line('');
            
            // Check if all expected columns are present
            $missingColumns = [];
            $existingColumns = [];
            
            foreach ($columns as $column) {
                $existingColumns[] = $column->Field;
            }
            
            foreach ($expectedColumns as $expectedColumn) {
                if (!in_array($expectedColumn, $existingColumns)) {
                    $missingColumns[] = $expectedColumn;
                }
            }
            
            if (empty($missingColumns)) {
                $this->info('✓ Todas las columnas esperadas están presentes en la tabla stores.');
            } else {
                $this->error('✗ Las siguientes columnas están ausentes en la tabla stores:');
                foreach ($missingColumns as $missingColumn) {
                    $this->line("  - {$missingColumn}");
                }
            }
            
            $this->line('');
            $this->info('Columnas actuales en la tabla stores:');
            foreach ($existingColumns as $column) {
                $this->line("  - {$column}");
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
