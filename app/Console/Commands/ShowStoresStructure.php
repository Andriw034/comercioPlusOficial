<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowStoresStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:stores-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the structure of the stores table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get the table structure
            $columns = DB::select('SHOW COLUMNS FROM stores');
            
            $this->info('Stores table structure:');
            $this->line('');
            
            foreach ($columns as $column) {
                $this->line("Column: {$column->Field}");
                $this->line("  Type: {$column->Type}");
                $this->line("  Null: {$column->Null}");
                $this->line("  Key: {$column->Key}");
                $this->line("  Default: {$column->Default}");
                $this->line("  Extra: {$column->Extra}");
                $this->line("");
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
