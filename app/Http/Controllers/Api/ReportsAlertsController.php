<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportsAlertsController extends Controller
{
    public function alerts(): JsonResponse
    {
        try {
            $store = $this->resolveMerchantStore();
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Tienda no encontrada',
                'data' => ['low_stock' => [], 'no_movement_30d' => [], 'top_growth' => []],
            ], 404);
        }

        try {
        $storeId = (int) $store->id;

        $lowStock = DB::table('products')
            ->where('store_id', $storeId)
            ->whereColumn('stock', '<=', 'reorder_point')
            ->where('reorder_point', '>', 0)
            ->select('id as product_id', 'name', 'stock', 'reorder_point')
            ->orderBy('stock')
            ->limit(20)
            ->get();

        $cutoff = now()->subDays(30)->toDateTimeString();

        $noMovement = DB::table('products as p')
            ->where('p.store_id', $storeId)
            ->leftJoin('order_products as op', 'op.product_id', '=', 'p.id')
            ->leftJoin('orders as o', function ($join) use ($storeId) {
                $join->on('o.id', '=', 'op.order_id')
                    ->where('o.store_id', '=', $storeId);
            })
            ->groupBy('p.id', 'p.name')
            ->select('p.id as product_id', 'p.name')
            ->selectRaw('MAX(o.created_at) as last_sale')
            ->havingRaw('MAX(o.created_at) IS NULL OR MAX(o.created_at) < ?', [$cutoff])
            ->orderByRaw('(MAX(o.created_at) IS NULL) DESC, MAX(o.created_at) ASC')
            ->limit(20)
            ->get();

        $thisMonthStart = now()->startOfMonth()->toDateTimeString();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
        $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth()->toDateTimeString();

        $thisMonth = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('o.store_id', $storeId)
            ->where('o.created_at', '>=', $thisMonthStart)
            ->whereIn('o.status', ['paid', 'approved', 'completed'])
            ->groupBy('op.product_id', 'p.name')
            ->select('op.product_id', 'p.name', DB::raw('SUM(op.quantity) as units'))
            ->get()
            ->keyBy('product_id');

        $lastMonth = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('o.store_id', $storeId)
            ->whereBetween('o.created_at', [$lastMonthStart, $lastMonthEnd])
            ->whereIn('o.status', ['paid', 'approved', 'completed'])
            ->groupBy('op.product_id', 'p.name')
            ->select('op.product_id', 'p.name', DB::raw('SUM(op.quantity) as units'))
            ->get()
            ->keyBy('product_id');

        $topGrowth = $thisMonth->map(function ($row) use ($lastMonth) {
            $lastUnits = (float) ($lastMonth->get($row->product_id)?->units ?? 0);
            $thisUnits = (float) $row->units;

            if ($lastUnits === 0.0) {
                $growthPct = $thisUnits > 0 ? 100.0 : 0.0;
            } else {
                $growthPct = round((($thisUnits - $lastUnits) / $lastUnits) * 100, 1);
            }

            return [
                'product_id' => $row->product_id,
                'name' => $row->name,
                'growth_pct' => $growthPct,
            ];
        })
            ->filter(fn ($r) => $r['growth_pct'] > 0)
            ->sortByDesc('growth_pct')
            ->values()
            ->take(10)
            ->toArray();

            return response()->json([
                'message' => 'Alertas de inventario y crecimiento',
                'data' => [
                    'low_stock' => $lowStock->toArray(),
                    'no_movement_30d' => $noMovement->toArray(),
                    'top_growth' => array_values($topGrowth),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Alertas de inventario y crecimiento',
                'data' => ['low_stock' => [], 'no_movement_30d' => [], 'top_growth' => []],
            ]);
        }
    }

    private function resolveMerchantStore()
    {
        $store = auth()->user()?->store()->first();

        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
    }
}
