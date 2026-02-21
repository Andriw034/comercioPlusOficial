<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesReport;
use App\Models\Store;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $query = SalesReport::forStore((int) $store->id)->latestFirst();

        if ($request->filled('type')) {
            $type = (string) $request->string('type');
            if (in_array($type, ['weekly', 'monthly', 'yearly'], true)) {
                $query->ofType($type);
            }
        }

        if ($request->filled('from')) {
            $query->where('start_date', '>=', (string) $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->where('end_date', '<=', (string) $request->string('to'));
        }

        $reports = $query->paginate($request->integer('per_page', 12));

        return response()->json([
            'message' => 'Reportes historicos',
            'data' => $reports->map(fn ($r) => $this->formatReport($r)),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function latest(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $type = (string) $request->string('type', 'monthly');
        $report = SalesReport::forStore((int) $store->id)->ofType($type)->latestFirst()->first();

        if (! $report) {
            return response()->json([
                'message' => 'No hay reportes generados aun para este periodo.',
                'data' => null,
            ]);
        }

        return response()->json([
            'message' => 'Ultimo reporte generado',
            'data' => $this->formatReport($report),
        ]);
    }

    public function generate(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $request->validate([
            'type' => ['required', 'in:weekly,monthly,yearly'],
            'date' => ['nullable', 'date'],
        ]);

        $referenceDate = $request->filled('date')
            ? Carbon::parse((string) $request->string('date'))
            : Carbon::now();

        $report = $this->reportService->generate(
            storeId: (int) $store->id,
            rangeType: (string) $request->string('type'),
            referenceDate: $referenceDate
        );

        return response()->json([
            'message' => 'Reporte generado correctamente.',
            'data' => $this->formatReport($report),
        ], 201);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
        ]);

        $store = $this->resolveMerchantStore();

        $data = $this->reportService->summary(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null
        );

        return response()->json([
            'message' => 'Resumen de reportes',
            'data' => $data,
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
            'group' => ['nullable', 'in:day,month'],
        ]);

        $store = $this->resolveMerchantStore();
        $group = (string) $request->string('group', 'day');

        $result = $this->reportService->salesSeries(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null,
            group: $group
        );

        return response()->json([
            'message' => 'Serie de ventas',
            'data' => $result['rows'],
            'meta' => [
                'group' => $result['group'],
                'from' => $result['from'],
                'to' => $result['to'],
                'status' => $result['status'],
            ],
        ]);
    }

    public function tax(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
            'group' => ['nullable', 'in:day,month'],
        ]);

        $store = $this->resolveMerchantStore();
        $group = (string) $request->string('group', 'day');

        $result = $this->reportService->taxReport(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null,
            group: $group
        );

        return response()->json([
            'message' => 'Reporte de IVA',
            'data' => [
                'summary' => $result['summary'],
                'breakdown' => $result['breakdown'],
            ],
            'meta' => [
                'group' => $result['group'],
                'from' => $result['from'],
                'to' => $result['to'],
                'status' => $result['status'],
            ],
        ]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'in:units,revenue'],
        ]);

        $store = $this->resolveMerchantStore();
        $limit = $request->integer('limit', 10);
        $sort = (string) $request->string('sort', 'units');

        $result = $this->reportService->topProducts(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null,
            limit: $limit,
            sort: $sort
        );

        return response()->json([
            'message' => 'Ranking de productos',
            'data' => $result['rows'],
            'meta' => [
                'sort' => $result['sort'],
                'limit' => $result['limit'],
                'from' => $result['from'],
                'to' => $result['to'],
                'status' => $result['status'],
            ],
        ]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $store = $this->resolveMerchantStore();

        $data = $this->reportService->inventoryReport(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null
        );

        return response()->json([
            'message' => 'Resumen de inventario para reportes',
            'data' => $data,
        ]);
    }

    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
        ]);

        $store = $this->resolveMerchantStore();
        $rows = $this->reportService->salesExportRows(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null
        );

        $filename = 'sales_report_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'wb');
            if (! $stream) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, [
                'invoice_number',
                'invoice_date',
                'order_id',
                'status',
                'payment_method',
                'customer_name',
                'customer_email',
                'net_sales',
                'tax_total',
                'gross_sales',
                'currency',
            ]);

            foreach ($rows as $row) {
                fputcsv($stream, [
                    $row['invoice_number'] ?? '',
                    $row['invoice_date'] ?? '',
                    $row['order_id'] ?? '',
                    $row['status'] ?? '',
                    $row['payment_method'] ?? '',
                    $row['customer_name'] ?? '',
                    $row['customer_email'] ?? '',
                    $row['net_sales'] ?? 0,
                    $row['tax_total'] ?? 0,
                    $row['gross_sales'] ?? 0,
                    $row['currency'] ?? 'COP',
                ]);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportTaxCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,pending,processing,paid,approved,completed,cancelled'],
        ]);

        $store = $this->resolveMerchantStore();
        $rows = $this->reportService->taxExportRows(
            storeId: (int) $store->id,
            from: $request->filled('from') ? (string) $request->string('from') : null,
            to: $request->filled('to') ? (string) $request->string('to') : null,
            status: $request->filled('status') ? (string) $request->string('status') : null
        );

        $filename = 'tax_report_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'wb');
            if (! $stream) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['period', 'net_sales', 'tax_total', 'gross_sales', 'orders_count']);

            foreach ($rows as $row) {
                fputcsv($stream, [
                    $row['period'] ?? '',
                    $row['net_sales'] ?? 0,
                    $row['tax_total'] ?? 0,
                    $row['gross_sales'] ?? 0,
                    $row['orders_count'] ?? 0,
                ]);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function formatReport(SalesReport $report): array
    {
        return [
            'id' => $report->id,
            'range_type' => $report->range_type,
            'period_label' => $report->period_label,
            'start_date' => $report->start_date?->toDateString(),
            'end_date' => $report->end_date?->toDateString(),
            'currency' => $report->currency,
            'orders_count' => $report->orders_count,
            'ventas_brutas' => $report->ventas_brutas,
            'iva_cobrado' => $report->iva_cobrado,
            'ventas_netas' => $report->ventas_netas,
            'cogs' => $report->cogs,
            'utilidad_bruta' => $report->utilidad_bruta,
            'utilidad_neta' => $report->utilidad_neta,
            'margen_bruto_pct' => $report->margen_bruto_pct,
            'top_products' => $report->top_products,
            'generated_at' => $report->generated_at?->toIso8601String(),
        ];
    }

    private function authorizeStore(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para acceder a esta tienda.');
        }
    }

    private function resolveMerchantStore(): Store
    {
        $store = auth()->user()?->store()->first();

        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
    }
}
