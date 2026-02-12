<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function summary(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $storeId = $request->get('store_id');

        $query = DB::table('orders')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $stats = $query->selectRaw('
                COUNT(*) as total_pedidos,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as ventas_pagadas,
                SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as ingresos,
                AVG(CASE WHEN status = "paid" THEN total_amount ELSE NULL END) as ticket_promedio
            ')
            ->first();

        return response()->json([
            'total_pedidos' => (int) $stats->total_pedidos,
            'ventas_pagadas' => (int) $stats->ventas_pagadas,
            'ingresos' => (float) $stats->ingresos,
            'ticket_promedio' => (float) $stats->ticket_promedio,
        ]);
    }

    public function timeseries(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $storeId = $request->get('store_id');

        $query = DB::table('orders')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', 'paid')
            ->selectRaw('DATE(created_at) as dia, SUM(total_amount) as ingresos')
            ->groupBy('dia')
            ->orderBy('dia');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function topProducts(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $storeId = $request->get('store_id');
        $limit = $request->get('limit', 5);

        $query = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('orders.status', 'paid')
            ->selectRaw('
                order_products.product_id,
                products.name as product_name,
                SUM(order_products.quantity) as unidades,
                SUM(order_products.price * order_products.quantity) as total
            ')
            ->groupBy('order_products.product_id', 'products.name')
            ->orderBy('total', 'desc')
            ->limit($limit);

        if ($storeId) {
            $query->where('orders.store_id', $storeId);
        }

        $data = $query->get();

        return response()->json($data);
    }
}
