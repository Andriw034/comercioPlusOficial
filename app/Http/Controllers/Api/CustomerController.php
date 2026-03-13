<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use App\Services\OrderBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function __construct(private readonly OrderBillingService $orderBillingService)
    {
    }

    public function registerCustomer(Request $request)
    {
        $storeId = $request->input('store_id', $request->input('storeId'));
        $store = $storeId ? Store::find($storeId) : null;

        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        return $this->persistVisit($request, $store);
    }

    public function registerVisit(Request $request, Store $store)
    {
        return $this->persistVisit($request, $store);
    }

    private function persistVisit(Request $request, Store $store)
    {
        $user = $request->user();
        if (! $user || ! $user->isClient()) {
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

        if (Schema::hasColumn('customers', 'is_active')) {
            $customer->is_active = true;
        }

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
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        // Backfill para incluir clientes con pedidos historicos.
        // No reactiva registros inactivos eliminados por el comerciante.
        try {
            $this->orderBillingService->syncStoreCustomersFromOrders((int) $store->id);
        } catch (\Throwable $e) {
            report($e);
        }

        $baseQuery = Customer::where('store_id', $store->id);
        if (Schema::hasColumn('customers', 'is_active')) {
            $baseQuery->where('is_active', true);
        }

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

    public function destroy(Request $request, Customer $customer)
    {
        $store = Store::where('user_id', $request->user()->id)->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $customer->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (Schema::hasColumn('customers', 'is_active')) {
            $customer->is_active = false;
            $customer->save();
        } else {
            $customer->delete();
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Cliente eliminado del directorio',
        ]);
    }
}
