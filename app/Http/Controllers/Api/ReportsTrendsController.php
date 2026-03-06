<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportsTrendsController extends Controller
{
    public function trends(): JsonResponse
    {
        $store = $this->resolveMerchantStore();
        $storeId = (int) $store->id;
        $since = now()->subMonths(12)->startOfMonth()->toDateTimeString();

        $salesByMonth = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereIn('status', ['paid', 'approved', 'completed'])
            ->where('created_at', '>=', $since)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COALESCE(SUM(total), 0) as total')
            )
            ->orderBy('month')
            ->get();

        $topCategories = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.store_id', $storeId)
            ->whereIn('o.status', ['paid', 'approved', 'completed'])
            ->where('o.created_at', '>=', $since)
            ->groupBy('c.id', 'c.name')
            ->select(
                DB::raw("COALESCE(c.name, 'Sin categoría') as name"),
                DB::raw('COALESCE(SUM(op.total_line), 0) as total')
            )
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Tendencias de ventas',
            'data' => [
                'sales_by_month' => $salesByMonth->toArray(),
                'top_categories' => $topCategories->toArray(),
            ],
        ]);
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
