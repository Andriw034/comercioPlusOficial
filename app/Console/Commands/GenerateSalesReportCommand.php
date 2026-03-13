<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateSalesReportCommand extends Command
{
    protected $signature = 'reports:generate
                            {--type=monthly : Tipo de reporte (weekly|monthly|yearly)}
                            {--store= : ID de tienda especifica}
                            {--date= : Fecha de referencia YYYY-MM-DD}';

    protected $description = 'Genera reportes de ventas y utilidad por tienda';

    public function __construct(private readonly ReportService $reportService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $storeId = $this->option('store');
        $date = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))
            : Carbon::now();

        if (! in_array($type, ['weekly', 'monthly', 'yearly'], true)) {
            $this->error("Tipo invalido: {$type}");
            return self::FAILURE;
        }

        if ($storeId) {
            $store = Store::find($storeId);
            if (! $store) {
                $this->error("Tienda #{$storeId} no encontrada.");
                return self::FAILURE;
            }

            $report = $this->reportService->generate((int) $store->id, $type, $date);
            $this->info("Reporte generado para tienda #{$store->id}: {$report->period_label}");
            return self::SUCCESS;
        }

        $count = $this->reportService->generateForAllStores($type, $date);
        $this->info("Reportes generados para {$count} tienda(s).");

        return self::SUCCESS;
    }
}
