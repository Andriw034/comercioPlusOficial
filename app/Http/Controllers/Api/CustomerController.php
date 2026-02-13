<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function registerVisit(Request $request, Store $store)
    {
        $user = $request->user();
        if (!$user || !$user->isClient()) {
            return response()->json(['message' => 'Solo clientes pueden ser registrados'], 403);
        }

        $now = now();
        $customer = Customer::firstOrCreate(
            [
                'store_id' => $store->id,
                'user_id' => $user->id,
            ],
            [
                'first_visited_at' => $now,
            ]
        );
        $customer->last_visited_at = $now;
        $customer->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Visita registrada',
        ]);
    }

    public function myCustomers(Request $request)
    {
        $store = Store::where('user_id', $request->user()->id)->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $baseQuery = Customer::where('store_id', $store->id);
        $customers = (clone $baseQuery)
            ->with('user:id,name,email,phone')
            ->orderByDesc('last_visited_at')
            ->paginate(20);

        $monthStart = now()->startOfMonth();
        $stats = [
            'total_customers' => (clone $baseQuery)->count(),
            'new_this_month' => (clone $baseQuery)->where('first_visited_at', '>=', $monthStart)->count(),
            'with_orders' => (clone $baseQuery)->where('total_orders', '>', 0)->count(),
            'total_revenue' => (float) ((clone $baseQuery)->sum('total_spent') ?? 0),
        ];

        return response()->json([
            'status' => 'ok',
            'data' => $customers,
            'stats' => $stats,
        ]);
    }
}
