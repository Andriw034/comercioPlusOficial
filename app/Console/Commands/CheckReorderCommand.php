<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\ReorderService;
use Illuminate\Console\Command;

class CheckReorderCommand extends Command
{
    protected $signature = 'inventory:check-reorder
                            {--store= : ID de tienda especifica}';

    protected $description = 'Verifica productos bajo stock y muestra sugerencias de reposicion';

    public function __construct(private readonly ReorderService $reorderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $storeId = $this->option('store');
        $stores = $storeId
            ? Store::where('id', $storeId)->get()
            : Store::select('id', 'name')->get();

        $total = 0;
        foreach ($stores as $store) {
            $suggestions = $this->reorderService->getSuggestions((int) $store->id);
            if ($suggestions->isEmpty()) {
                continue;
            }

            $total += $suggestions->count();
            $this->warn("Tienda {$store->name}: {$suggestions->count()} producto(s) bajo stock.");
        }

        if ($total === 0) {
            $this->info('Todos los productos tienen stock suficiente.');
        } else {
            $this->warn("Total de alertas: {$total}");
        }

        return self::SUCCESS;
    }
}
