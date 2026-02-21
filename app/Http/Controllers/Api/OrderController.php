<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function __construct(private readonly OrderBillingService $orderBillingService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::with(['user', 'store', 'ordenproducts.product'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        return response()->json([
            'message' => 'Lista de ordenes',
            'data' => $orders->map(fn (Order $order) => $this->serializeOrder($order)),
            'meta' => [
                'count' => $orders->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No aplica para API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'date' => 'nullable|date',
            'payment_method' => 'required|string|max:255',
            'status' => 'nullable|in:pending,processing,paid,approved,cancelled,completed',
        ]);

        $order = $this->orderBillingService->createOrder($validated, (int) $request->user()->id);

        return response()->json([
            'message' => 'Orden creada correctamente.',
            'data' => $this->serializeOrder($order),
            'meta' => [
                'subtotal' => (float) ($order->subtotal ?? 0),
                'tax_total' => (float) ($order->tax_total ?? 0),
                'total' => (float) ($order->total ?? 0),
                'currency' => (string) ($order->currency ?? 'COP'),
            ],
            'summary' => [
                'subtotal' => (float) ($order->subtotal ?? 0),
                'tax_total' => (float) ($order->tax_total ?? 0),
                'total' => (float) ($order->total ?? 0),
                'currency' => (string) ($order->currency ?? 'COP'),
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['user', 'store', 'ordenproducts.product'])->find($id);

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $user = auth()->user();
        $isOwner = $user && (int) $order->user_id === (int) $user->id;
        $isMerchantOfStore = $user && $user->store && (int) $order->store_id === (int) $user->store->id;
        if (! $isOwner && ! $isMerchantOfStore) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'message' => 'Detalle de orden',
            'data' => $this->serializeOrder($order),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // No aplica para API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::with(['user', 'store', 'ordenproducts.product'])->find($id);

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $user = $request->user();
        $isOwner = (int) $order->user_id === (int) $user->id;
        $isMerchantOfStore = $user->store && (int) $order->store_id === (int) $user->store->id;
        if (! $isOwner && ! $isMerchantOfStore) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'store_id' => 'sometimes|exists:stores,id',
            'total' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,processing,paid,approved,cancelled,completed',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Orden actualizada correctamente.',
            'data' => $this->serializeOrder($order->fresh(['user', 'store', 'ordenproducts.product'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $user = auth()->user();
        $isOwner = $user && (int) $order->user_id === (int) $user->id;
        $isMerchantOfStore = $user && $user->store && (int) $order->store_id === (int) $user->store->id;
        if (! $isOwner && ! $isMerchantOfStore) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $order->delete();

        return response()->json([
            'message' => 'Orden eliminada correctamente.',
        ]);
    }

    /**
     * Historial de ordenes del comerciante (por su tienda)
     */
    public function merchantIndex(Request $request)
    {
        $store = $request->user()->store()->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $allowedStatuses = ['pending', 'processing', 'paid', 'approved', 'cancelled', 'completed'];
        $perPage = $request->integer('per_page', 20);
        $perPage = max(1, min(50, $perPage));

        $query = Order::query()
            ->with(['user', 'store', 'ordenproducts.product'])
            ->where('store_id', $store->id)
            ->latest('id');

        if ($request->filled('status')) {
            $status = strtolower((string) $request->string('status'));
            if (in_array($status, $allowedStatuses, true)) {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'message' => 'Lista de ordenes',
            'data' => $orders->map(fn (Order $order) => $this->serializeOrder($order)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Actualizar estado de orden (comerciante)
     */
    public function updateStatus(Request $request, string $id)
    {
        $order = Order::find($id);
        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $store = $request->user()->store()->first();
        if (! $store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,paid,approved,cancelled,completed',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'data' => $this->serializeOrder($order->fresh(['user', 'store', 'ordenproducts.product'])),
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        $items = $order->ordenproducts->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) ($item->unit_price ?? 0),
                'line_subtotal' => (float) ($item->base_price ?? $item->line_subtotal ?? 0),
                'tax_amount' => (float) ($item->tax_amount ?? 0),
                'tax_rate_applied' => (float) ($item->tax_rate_applied ?? 0),
                'line_total' => (float) ($item->total_line ?? $item->line_total ?? 0),
                'image_url' => $item->product?->image_url ?? $item->product?->image ?? null,
            ];
        })->values()->all();

        $subtotal = Schema::hasColumn('orders', 'subtotal')
            ? (float) ($order->subtotal ?? 0)
            : (float) collect($items)->sum('line_subtotal');
        $taxTotal = Schema::hasColumn('orders', 'tax_total')
            ? (float) ($order->tax_total ?? 0)
            : (float) collect($items)->sum('tax_amount');
        $total = (float) ($order->total ?? ($subtotal + $taxTotal));

        return [
            'id' => (int) $order->id,
            'invoice_number' => $order->invoice_number,
            'invoice_date' => $order->invoice_date ?? $order->date ?? $order->created_at,
            'date' => $order->date ?? $order->created_at,
            'status' => (string) $order->status,
            'payment_method' => $order->payment_method,
            'store_id' => (int) $order->store_id,
            'store_name' => $order->store?->name,
            'customer_id' => (int) $order->user_id,
            'customer_name' => $order->user?->name,
            'customer_email' => $order->user?->email,
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
            'currency' => $order->currency ?? 'COP',
            'items' => $items,
        ];
    }
}
