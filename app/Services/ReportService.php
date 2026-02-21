<?php

namespace App\Services;

use App\Models\SalesReport;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function generate(int $storeId, string $rangeType, Carbon $referenceDate): SalesReport
    {
        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($rangeType, $referenceDate);
        $totals = $this->computeTotals($storeId, $startDate, $endDate);

        return SalesReport::updateOrCreate(
            [
                'store_id' => $storeId,
                'range_type' => $rangeType,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            [
                'period_label' => $periodLabel,
                'currency' => 'COP',
                'totals_json' => $totals,
                'generated_at' => now(),
            ]
        );
    }

    public function generateForAllStores(string $rangeType, Carbon $referenceDate): int
    {
        $count = 0;
        Store::select('id')->chunk(50, function ($stores) use ($rangeType, $referenceDate, &$count) {
            foreach ($stores as $store) {
                try {
                    $this->generate((int) $store->id, $rangeType, $referenceDate);
                    $count++;
                } catch (\Throwable $e) {
                    Log::error('ReportService error', [
                        'store_id' => $store->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $count;
    }

    public function summary(int $storeId, ?string $from, ?string $to, ?string $status): array
    {
        [$start, $end] = $this->resolveRange($from, $to);
        $resolvedStatus = $this->resolveStatus($status, 'paid');

        $ordersQuery = DB::table('orders as o')->where('o.store_id', $storeId);
        $this->applyDateFilter($ordersQuery, $start, $end, 'o');
        $this->applyStatusFilter($ordersQuery, $resolvedStatus, 'o');

        $ordersAgg = $ordersQuery
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COUNT(DISTINCT o.user_id) as unique_customers')
            ->selectRaw('COALESCE(SUM(o.total), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(o.subtotal), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(o.tax_total), 0) as tax_total')
            ->first();

        $itemsQuery = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->where('o.store_id', $storeId);
        $this->applyDateFilter($itemsQuery, $start, $end, 'o');
        $this->applyStatusFilter($itemsQuery, $resolvedStatus, 'o');

        $itemsSold = (int) ($itemsQuery->selectRaw('COALESCE(SUM(op.quantity), 0) as items_sold')->value('items_sold') ?? 0);

        $ordersCount = (int) ($ordersAgg->orders_count ?? 0);
        $grossSales = (float) ($ordersAgg->gross_sales ?? 0);
        $netSales = (float) ($ordersAgg->net_sales ?? 0);
        $taxTotal = (float) ($ordersAgg->tax_total ?? 0);

        return [
            'gross_sales' => round($grossSales, 2),
            'net_sales' => round($netSales, 2),
            'tax_total' => round($taxTotal, 2),
            'orders_count' => $ordersCount,
            'avg_ticket' => $ordersCount > 0 ? round($grossSales / $ordersCount, 2) : 0.0,
            'items_sold' => $itemsSold,
            'unique_customers' => (int) ($ordersAgg->unique_customers ?? 0),
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'status' => $resolvedStatus ?? 'all',
        ];
    }

    public function salesSeries(int $storeId, ?string $from, ?string $to, ?string $status, string $group): array
    {
        [$start, $end] = $this->resolveRange($from, $to);
        $resolvedStatus = $this->resolveStatus($status, 'paid');
        $resolvedGroup = $group === 'month' ? 'month' : 'day';

        $periodExpr = $resolvedGroup === 'month'
            ? "DATE_FORMAT(COALESCE(o.invoice_date, o.created_at), '%Y-%m')"
            : "DATE_FORMAT(COALESCE(o.invoice_date, o.created_at), '%Y-%m-%d')";

        $query = DB::table('orders as o')
            ->where('o.store_id', $storeId);

        $this->applyDateFilter($query, $start, $end, 'o');
        $this->applyStatusFilter($query, $resolvedStatus, 'o');

        $rows = $query
            ->selectRaw("{$periodExpr} as period")
            ->selectRaw('COALESCE(SUM(o.total), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(o.subtotal), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(o.tax_total), 0) as tax_total')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                'period' => (string) $row->period,
                'gross_sales' => round((float) $row->gross_sales, 2),
                'net_sales' => round((float) $row->net_sales, 2),
                'tax_total' => round((float) $row->tax_total, 2),
                'orders_count' => (int) $row->orders_count,
            ])
            ->values()
            ->all();

        return [
            'group' => $resolvedGroup,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'status' => $resolvedStatus ?? 'all',
            'rows' => $rows,
        ];
    }

    public function taxReport(int $storeId, ?string $from, ?string $to, ?string $status, string $group = 'day'): array
    {
        [$start, $end] = $this->resolveRange($from, $to);
        $resolvedStatus = $this->resolveStatus($status, 'paid');

        $summaryQuery = DB::table('orders as o')->where('o.store_id', $storeId);
        $this->applyDateFilter($summaryQuery, $start, $end, 'o');
        $this->applyStatusFilter($summaryQuery, $resolvedStatus, 'o');

        $summary = $summaryQuery
            ->selectRaw('COALESCE(SUM(o.subtotal), 0) as base')
            ->selectRaw('COALESCE(SUM(o.tax_total), 0) as iva')
            ->selectRaw('COALESCE(SUM(o.total), 0) as total')
            ->first();

        $series = $this->salesSeries($storeId, $from, $to, $status, $group)['rows'];

        return [
            'summary' => [
                'base' => round((float) ($summary->base ?? 0), 2),
                'iva' => round((float) ($summary->iva ?? 0), 2),
                'total' => round((float) ($summary->total ?? 0), 2),
            ],
            'breakdown' => $series,
            'group' => $group === 'month' ? 'month' : 'day',
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'status' => $resolvedStatus ?? 'all',
        ];
    }

    public function topProducts(
        int $storeId,
        ?string $from,
        ?string $to,
        ?string $status,
        int $limit,
        string $sort
    ): array {
        [$start, $end] = $this->resolveRange($from, $to);
        $resolvedStatus = $this->resolveStatus($status, 'paid');
        $resolvedLimit = max(1, min(100, $limit));
        $resolvedSort = $sort === 'units' ? 'units' : 'revenue';

        $query = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('o.store_id', $storeId);

        $this->applyDateFilter($query, $start, $end, 'o');
        $this->applyStatusFilter($query, $resolvedStatus, 'o');

        $query
            ->select('op.product_id', 'p.name')
            ->selectRaw('COALESCE(SUM(op.quantity), 0) as units_sold')
            ->selectRaw('COALESCE(SUM(op.total_line), 0) as revenue')
            ->groupBy('op.product_id', 'p.name');

        if ($resolvedSort === 'units') {
            $query->orderByDesc('units_sold')->orderByDesc('revenue');
        } else {
            $query->orderByDesc('revenue')->orderByDesc('units_sold');
        }

        $rows = $query
            ->limit($resolvedLimit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'name' => (string) $row->name,
                'units_sold' => (int) $row->units_sold,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'sort' => $resolvedSort,
            'limit' => $resolvedLimit,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'status' => $resolvedStatus ?? 'all',
        ];
    }

    public function inventoryReport(int $storeId, ?string $from, ?string $to): array
    {
        [$start, $end] = $this->resolveRange($from, $to);

        $query = DB::table('inventory_movements as im')->where('im.store_id', $storeId);
        $query->whereBetween('im.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        $summary = (clone $query)
            ->selectRaw('COALESCE(SUM(CASE WHEN im.quantity > 0 THEN im.quantity ELSE 0 END), 0) as entradas')
            ->selectRaw('ABS(COALESCE(SUM(CASE WHEN im.quantity < 0 THEN im.quantity ELSE 0 END), 0)) as salidas')
            ->selectRaw('COALESCE(SUM(CASE WHEN im.type = "adjustment" THEN ABS(im.quantity) ELSE 0 END), 0) as ajustes')
            ->first();

        $topOut = (clone $query)
            ->join('products as p', 'p.id', '=', 'im.product_id')
            ->where('im.type', 'sale')
            ->where('im.quantity', '<', 0)
            ->select('im.product_id', 'p.name')
            ->selectRaw('ABS(COALESCE(SUM(im.quantity), 0)) as units_out')
            ->groupBy('im.product_id', 'p.name')
            ->orderByDesc('units_out')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'name' => (string) $row->name,
                'units_out' => (int) $row->units_out,
            ])
            ->values()
            ->all();

        $avgStock = (float) (DB::table('products')->where('store_id', $storeId)->avg('stock') ?? 0);
        $salidas = (float) ($summary->salidas ?? 0);

        return [
            'entries' => (int) ($summary->entradas ?? 0),
            'outs' => (int) ($summary->salidas ?? 0),
            'adjustments' => (int) ($summary->ajustes ?? 0),
            'avg_stock' => round($avgStock, 2),
            'rotation_approx' => $avgStock > 0 ? round($salidas / $avgStock, 4) : null,
            'top_out_products' => $topOut,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
        ];
    }

    public function salesExportRows(int $storeId, ?string $from, ?string $to, ?string $status): array
    {
        [$start, $end] = $this->resolveRange($from, $to);
        $resolvedStatus = $this->resolveStatus($status, 'paid');

        $query = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->where('o.store_id', $storeId);

        $this->applyDateFilter($query, $start, $end, 'o');
        $this->applyStatusFilter($query, $resolvedStatus, 'o');

        return $query
            ->orderByRaw('COALESCE(o.invoice_date, o.created_at) ASC')
            ->select([
                'o.id',
                'o.invoice_number',
                'o.status',
                'o.payment_method',
                'o.currency',
                'u.name as customer_name',
                'u.email as customer_email',
            ])
            ->selectRaw('COALESCE(o.invoice_date, o.created_at) as invoice_date')
            ->selectRaw('COALESCE(o.subtotal, 0) as net_sales')
            ->selectRaw('COALESCE(o.tax_total, 0) as tax_total')
            ->selectRaw('COALESCE(o.total, 0) as gross_sales')
            ->get()
            ->map(fn ($row) => [
                'invoice_number' => (string) ($row->invoice_number ?: ('PED-'.$row->id)),
                'invoice_date' => (string) $row->invoice_date,
                'order_id' => (int) $row->id,
                'status' => (string) $row->status,
                'payment_method' => (string) ($row->payment_method ?? ''),
                'customer_name' => (string) ($row->customer_name ?? ''),
                'customer_email' => (string) ($row->customer_email ?? ''),
                'net_sales' => round((float) $row->net_sales, 2),
                'tax_total' => round((float) $row->tax_total, 2),
                'gross_sales' => round((float) $row->gross_sales, 2),
                'currency' => (string) ($row->currency ?? 'COP'),
            ])
            ->values()
            ->all();
    }

    public function taxExportRows(int $storeId, ?string $from, ?string $to, ?string $status): array
    {
        $series = $this->salesSeries($storeId, $from, $to, $status, 'day')['rows'];

        return collect($series)
            ->map(fn ($row) => [
                'period' => (string) $row['period'],
                'net_sales' => round((float) ($row['net_sales'] ?? 0), 2),
                'tax_total' => round((float) ($row['tax_total'] ?? 0), 2),
                'gross_sales' => round((float) ($row['gross_sales'] ?? 0), 2),
                'orders_count' => (int) ($row['orders_count'] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function computeTotals(int $storeId, Carbon $start, Carbon $end): array
    {
        $paidStatuses = ['paid', 'approved', 'completed'];
        $from = $start->copy()->startOfDay()->toDateTimeString();
        $to = $end->copy()->endOfDay()->toDateTimeString();

        $rows = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('o.store_id', $storeId)
            ->whereIn('o.status', $paidStatuses)
            ->whereBetween('o.created_at', [$from, $to])
            ->select([
                DB::raw('COUNT(DISTINCT o.id) as orders_count'),
                DB::raw('SUM(COALESCE(op.total_line, op.unit_price * op.quantity)) as ventas_brutas'),
                DB::raw('SUM(COALESCE(op.tax_amount, 0)) as iva_cobrado'),
                DB::raw('SUM(COALESCE(op.base_price, op.unit_price * op.quantity)) as ventas_netas'),
                DB::raw('SUM(COALESCE(p.cost_price, 0) * op.quantity) as cogs'),
            ])
            ->first();

        $ventasNetas = (float) ($rows->ventas_netas ?? 0);
        $cogs = (float) ($rows->cogs ?? 0);
        $utilidadBruta = $ventasNetas - $cogs;
        $margen = $ventasNetas > 0 ? round(($utilidadBruta / $ventasNetas) * 100, 2) : 0.0;

        $topProducts = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('o.store_id', $storeId)
            ->whereIn('o.status', $paidStatuses)
            ->whereBetween('o.created_at', [$from, $to])
            ->groupBy('p.id', 'p.name')
            ->select([
                'p.id',
                'p.name',
                DB::raw('SUM(op.quantity) as total_qty'),
                DB::raw('SUM(COALESCE(op.base_price, op.unit_price * op.quantity)) as total_neto'),
            ])
            ->orderByDesc('total_neto')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'product_id' => (int) $r->id,
                'name' => (string) $r->name,
                'total_qty' => (int) $r->total_qty,
                'total_neto' => round((float) $r->total_neto, 2),
            ])
            ->toArray();

        return [
            'orders_count' => (int) ($rows->orders_count ?? 0),
            'ventas_brutas' => round((float) ($rows->ventas_brutas ?? 0), 2),
            'iva_cobrado' => round((float) ($rows->iva_cobrado ?? 0), 2),
            'ventas_netas' => round($ventasNetas, 2),
            'cogs' => round($cogs, 2),
            'utilidad_bruta' => round($utilidadBruta, 2),
            'gastos' => 0,
            'utilidad_neta' => round($utilidadBruta, 2),
            'margen_bruto_pct' => $margen,
            'top_products' => $topProducts,
        ];
    }

    private function resolveDateRange(string $rangeType, Carbon $date): array
    {
        return match ($rangeType) {
            SalesReport::RANGE_WEEKLY => [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek(),
                'Semana '.$date->weekOfYear.' '.$date->year,
            ],
            SalesReport::RANGE_MONTHLY => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
                $date->translatedFormat('F Y'),
            ],
            SalesReport::RANGE_YEARLY => [
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
                (string) $date->year,
            ],
            default => throw new \InvalidArgumentException("range_type invalido: {$rangeType}"),
        };
    }

    private function resolveRange(?string $from, ?string $to): array
    {
        if (empty($from) && empty($to)) {
            $end = Carbon::now()->endOfDay();
            $start = Carbon::now()->subDays(29)->startOfDay();
            return [$start, $end];
        }

        if (! empty($from) && ! empty($to)) {
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ];
        }

        if (! empty($from)) {
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::now()->endOfDay(),
            ];
        }

        $toDate = Carbon::parse((string) $to)->endOfDay();

        return [
            $toDate->copy()->subDays(29)->startOfDay(),
            $toDate,
        ];
    }

    private function resolveStatus(?string $status, string $default = 'paid'): ?string
    {
        $normalized = strtolower(trim((string) ($status ?? '')));
        if ($normalized === '' && $default !== '') {
            return $default;
        }

        if ($normalized === '' || $normalized === 'all') {
            return null;
        }

        return $normalized;
    }

    private function applyDateFilter(Builder $query, Carbon $start, Carbon $end, string $alias): void
    {
        $query->whereRaw(
            "COALESCE({$alias}.invoice_date, {$alias}.created_at) BETWEEN ? AND ?",
            [$start->toDateTimeString(), $end->toDateTimeString()]
        );
    }

    private function applyStatusFilter(Builder $query, ?string $status, string $alias): void
    {
        if ($status !== null) {
            $query->where("{$alias}.status", $status);
        }
    }
}
