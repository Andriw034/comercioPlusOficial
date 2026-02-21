<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function summary(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();
        $threshold = $request->integer('threshold') ?: null;
        $perPage = $request->integer('per_page', 25);

        $productsQuery = Product::query()
            ->where('store_id', $store->id)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $productsQuery->where('name', 'like', "%{$search}%");
        }

        $products = $productsQuery->paginate($perPage);

        $allProducts = Product::query()->where('store_id', $store->id)->get(['id', 'stock', 'reorder_point', 'price']);
        $lowStockCount = $allProducts->filter(function ($product) use ($threshold) {
            $limit = $threshold ?? (int) $product->reorder_point;
            return (int) $product->stock <= $limit;
        })->count();

        $outOfStockCount = $allProducts->filter(fn ($product) => (int) $product->stock <= 0)->count();
        $inventoryValue = $allProducts->sum(fn ($product) => (float) $product->price * max(0, (int) $product->stock));

        return response()->json([
            'message' => 'Resumen de inventario',
            'data' => $products->map(function (Product $product) use ($threshold) {
                $limit = $threshold ?? (int) $product->reorder_point;

                return [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'stock' => (int) $product->stock,
                    'reorder_point' => (int) $product->reorder_point,
                    'allow_backorder' => (bool) $product->allow_backorder,
                    'cost_price' => (float) ($product->cost_price ?? 0),
                    'price' => (float) $product->price,
                    'is_low_stock' => (int) $product->stock <= $limit,
                    'deficit' => max(0, $limit - (int) $product->stock),
                ];
            }),
            'stats' => [
                'total_products' => (int) $allProducts->count(),
                'low_stock_products' => (int) $lowStockCount,
                'out_of_stock_products' => (int) $outOfStockCount,
                'inventory_value' => round((float) $inventoryValue, 2),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'stats' => [
                    'total_products' => (int) $allProducts->count(),
                    'low_stock_products' => (int) $lowStockCount,
                    'out_of_stock_products' => (int) $outOfStockCount,
                    'inventory_value' => round((float) $inventoryValue, 2),
                ],
            ],
        ]);
    }

    public function merchantMovements(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();

        $query = InventoryMovement::forStore((int) $store->id)
            ->with(['product:id,name', 'creator:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('type')) {
            $type = (string) $request->string('type');
            if (in_array($type, ['sale', 'purchase', 'adjustment', 'return', 'cancel'], true)) {
                $query->where('type', $type);
            }
        }

        $movements = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'message' => 'Kardex de inventario',
            'data' => $movements->map(fn (InventoryMovement $movement) => $this->formatMovement($movement)),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'total' => $movements->total(),
                'per_page' => $movements->perPage(),
            ],
        ]);
    }

    public function merchantAdjust(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $product = $store->products()->findOrFail((int) $validated['product_id']);
        $stockBefore = (int) $product->stock;

        $movement = $this->inventoryService->recordAdjustment(
            product: $product,
            delta: (int) $validated['delta'],
            reason: (string) $validated['reason'],
            storeId: (int) $store->id,
            actorId: (int) auth()->id(),
        );

        return response()->json([
            'message' => "Stock de '{$product->name}' ajustado correctamente.",
            'data' => [
                'product_id' => (int) $product->id,
                'product' => $product->name,
                'stock_antes' => $stockBefore,
                'delta' => (int) $movement->quantity,
                'stock_ahora' => (int) $movement->stock_after,
                'movement_id' => (int) $movement->id,
            ],
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();

        $query = Order::query()
            ->with(['user:id,name,email', 'ordenproducts.product:id,name'])
            ->where('store_id', $store->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        $orders = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'message' => 'Listado de facturas',
            'data' => $orders->map(function (Order $order) {
                return [
                    'id' => (int) $order->id,
                    'invoice_number' => $order->invoice_number,
                    'invoice_date' => $order->invoice_date ?? $order->date ?? $order->created_at,
                    'status' => (string) $order->status,
                    'payment_method' => $order->payment_method,
                    'customer_name' => $order->user?->name,
                    'customer_email' => $order->user?->email,
                    'subtotal' => (float) ($order->subtotal ?? 0),
                    'tax_total' => (float) ($order->tax_total ?? 0),
                    'total' => (float) ($order->total ?? 0),
                    'currency' => (string) ($order->currency ?? 'COP'),
                    'items_count' => (int) $order->ordenproducts->count(),
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    }

    public function lowStock(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $threshold = $request->integer('threshold') ?: null;
        $products = $this->inventoryService->getLowStockProducts((int) $store->id, $threshold);

        return response()->json([
            'message' => 'Productos con bajo stock',
            'data' => $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => (int) $product->stock,
                'reorder_point' => (int) $product->reorder_point,
                'cost_price' => (float) ($product->cost_price ?? 0),
                'price' => (float) $product->price,
                'deficit' => max(0, (int) $product->reorder_point - (int) $product->stock),
                'allow_backorder' => (bool) $product->allow_backorder,
            ]),
            'meta' => [
                'total' => $products->count(),
            ],
            'total' => $products->count(),
        ]);
    }

    public function movements(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);

        $query = InventoryMovement::forStore((int) $store->id)
            ->with(['product:id,name', 'creator:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('type')) {
            $type = (string) $request->string('type');
            if (in_array($type, ['sale', 'purchase', 'adjustment', 'return', 'cancel'], true)) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->inDateRange((string) $request->string('from'), (string) $request->string('to'));
        }

        $movements = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'message' => 'Kardex de inventario',
            'data' => $movements->map(fn (InventoryMovement $movement) => $this->formatMovement($movement)),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'total' => $movements->total(),
                'per_page' => $movements->perPage(),
            ],
        ]);
    }

    public function adjust(AdjustStockRequest $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $validated = $request->validated();
        $product = $store->products()->findOrFail($validated['product_id']);

        $movement = $this->inventoryService->adjust(
            productId: (int) $product->id,
            newStock: (int) $validated['new_stock'],
            note: (string) $validated['note'],
            storeId: (int) $store->id,
            actorId: (int) auth()->id(),
        );

        return response()->json([
            'message' => "Stock de '{$product->name}' ajustado correctamente.",
            'data' => [
                'product_id' => $product->id,
                'product' => $product->name,
                'stock_antes' => (int) $movement->stock_after - (int) $movement->quantity,
                'ajuste' => (int) $movement->quantity,
                'stock_ahora' => (int) $movement->stock_after,
            ],
        ]);
    }

    private function formatMovement(InventoryMovement $movement): array
    {
        $direction = $movement->quantity < 0 ? 'out' : ($movement->quantity > 0 ? 'in' : 'adjust');

        return [
            'id' => (int) $movement->id,
            'type' => $movement->type,
            'direction' => $direction,
            'quantity' => (int) $movement->quantity,
            'stock_after' => (int) $movement->stock_after,
            'unit_cost' => (float) ($movement->unit_cost ?? 0),
            'unit_price' => (float) ($movement->unit_price ?? 0),
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id,
            'order_id' => $movement->reference_type === 'order' ? (int) ($movement->reference_id ?? 0) : null,
            'reason' => $movement->note,
            'note' => $movement->note,
            'product' => $movement->product,
            'created_by' => $movement->creator?->name,
            'created_at' => $movement->created_at?->toIso8601String(),
        ];
    }

    private function resolveMerchantStore(): Store
    {
        $store = auth()->user()?->store()->first();

        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
    }

    private function authorizeStore(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para acceder a esta tienda.');
        }
    }
}
