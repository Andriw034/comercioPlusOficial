<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckProductsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:products-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the structure of the products table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get the table structure
            $columns = DB::select('SHOW COLUMNS FROM products');
            
            // Columns that should be in the products table
        $expectedColumns = [
    'id',
    'name',
    'description',
    'price',
    'stock',
    'image',           // <-- en tu BD actual es 'image' (no 'image_path')
    'category_id',
    'offer',
    'average_rating',
    'user_id',
    'store_id',
    'created_at',
    'updated_at',
];            $this->info('Verificando la estructura de la tabla products...');
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
                $this->info('✓ Todas las columnas esperadas están presentes en la tabla products.');
            } else {
                $this->error('✗ Las siguientes columnas están ausentes en la tabla products:');
                foreach ($missingColumns as $missingColumn) {
                    $this->line("  - {$missingColumn}");
                }
            }
            
            $this->line('');
            $this->info('Columnas actuales en la tabla products:');
            foreach ($existingColumns as $column) {
                $this->line("  - {$column}");
            }
            
            // Check foreign keys
            $this->line('');
            $this->info('Verificando claves foráneas en la tabla products...');
            $foreignKeys = DB::select("
                SELECT 
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    TABLE_SCHEMA = DATABASE() AND
                    TABLE_NAME = 'products' AND
                    REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (empty($foreignKeys)) {
                $this->error('✗ No se encontraron claves foráneas en la tabla products.');
            } else {
                $this->info('✓ Claves foráneas encontradas en la tabla products:');
                foreach ($foreignKeys as $foreignKey) {
                    $this->line("  - {$foreignKey->COLUMN_NAME} -> {$foreignKey->REFERENCED_TABLE_NAME}.{$foreignKey->REFERENCED_COLUMN_NAME}");
                }
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
