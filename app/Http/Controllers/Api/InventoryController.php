<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\InventoryImportService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly InventoryImportService $inventoryImportService
    ) {}

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx,xls'],
        ]);

        try {
            $store = $this->resolveMerchantStore();
            $data = $this->inventoryImportService->preview($store, $request->file('file'));
            return response()->json($data);
        } catch (\Throwable $e) {
            report($e);
            Log::error('[inventory-import] preview failed', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $status = $e instanceof \RuntimeException ? 422 : 500;

            return response()->json([
                'message' => 'No se pudo generar la vista previa del archivo.',
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ], $status);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx,xls'],
            'upsert' => ['nullable', 'boolean'],
        ]);

        try {
            $store = $this->resolveMerchantStore();
            $result = $this->inventoryImportService->import(
                $store,
                $request->file('file'),
                $request->boolean('upsert', true),
            );
        } catch (\Throwable $e) {
            report($e);
            Log::error('[inventory-import] import failed', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $status = $e instanceof \RuntimeException ? 422 : 500;

            return response()->json([
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => 1,
                'errors' => [[
                    'row' => 1,
                    'error' => $e->getMessage(),
                ]],
                'error_class' => get_class($e),
            ], $status);
        }

        $status = $result['success'] ? 200 : 422;
        return response()->json($result, $status);
    }

    public function template(Request $request)
    {
        $store = $this->resolveMerchantStore();
        $csv = $this->inventoryImportService->generateTemplateCsv($store);
        $filename = 'inventario-template-store-' . $store->id . '-' . now()->format('Ymd-His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

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

        $allProducts = Product::query()
            ->where('store_id', $store->id)
            ->get($this->inventoryStatsSelectColumns());

        $productsWithInventory = $allProducts->filter(fn ($product) => (int) $product->stock > 0)->count();
        $lowStockCount = $allProducts->filter(function ($product) use ($threshold) {
            $stock = (int) $product->stock;
            $limit = $threshold ?? (int) $product->reorder_point;

            // Bajo stock no incluye agotados para evitar doble conteo en los KPIs.
            return $stock > 0 && $stock <= $limit;
        })->count();

        $outOfStockCount = $allProducts->filter(fn ($product) => (int) $product->stock <= 0)->count();
        $normalStockCount = max(0, $productsWithInventory - $lowStockCount);
        $inventoryValue = $allProducts->sum(function ($product) {
            $stock = max(0, (int) $product->stock);
            $unitCost = $this->safeNumericColumn($product, 'cost_price');
            if ($unitCost <= 0) {
                $unitCost = $this->safeNumericColumn($product, 'sale_price', (float) ($product->price ?? 0));
            }
            return $unitCost * $stock;
        });
        $roundedInventoryValue = round((float) $inventoryValue, 2);

        $stats = [
            // Compatibilidad histórica.
            'total_products' => (int) $allProducts->count(),
            'low_stock_products' => (int) $lowStockCount,
            'out_of_stock_products' => (int) $outOfStockCount,
            'inventory_value' => $roundedInventoryValue,

            // Nuevos KPIs profesionales.
            'products_with_inventory' => (int) $productsWithInventory,
            'normal_stock_products' => (int) $normalStockCount,
            'inventory_total_value' => $roundedInventoryValue,
        ];

        return response()->json([
            'message' => 'Resumen profesional de inventario',
            'data' => $products->map(function (Product $product) use ($threshold) {
                $limit = $threshold ?? (int) $product->reorder_point;
                $stock = max(0, (int) $product->stock);
                $salePrice = $this->safeNumericColumn($product, 'sale_price', (float) ($product->price ?? 0));
                $costPrice = $this->safeNumericColumn($product, 'cost_price');
                $priceWithIva = round($salePrice * 1.19, 2);
                $isLowStock = $stock > 0 && $stock <= $limit;
                $stockStatus = $stock <= 0 ? 'agotado' : ($isLowStock ? 'bajo' : 'normal');

                return [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'ref_adicional' => $this->safeStringColumn($product, 'ref_adicional'),
                    'unit' => $this->safeStringColumn($product, 'unit', 'UND'),
                    'stock' => (int) $product->stock,
                    'reorder_point' => (int) $product->reorder_point,
                    'allow_backorder' => (bool) $this->safeNumericColumn($product, 'allow_backorder'),
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'price' => (float) $product->price,
                    'price_with_iva' => $priceWithIva,
                    'total_cost' => round($costPrice * $stock, 2),
                    'total_sale' => round($salePrice * $stock, 2),
                    'total_sale_with_iva' => round($priceWithIva * $stock, 2),
                    'stock_status' => $stockStatus,
                    'is_low_stock' => $isLowStock,
                    'deficit' => max(0, $limit - (int) $product->stock),
                ];
            }),
            'stats' => $stats,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'stats' => $stats,
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();
        $thresholdOverride = $request->integer('threshold');

        $products = Product::query()
            ->where('store_id', $store->id)
            ->get($this->inventoryStatsSelectColumns());

        $productsWithInventory = $products->filter(fn ($product) => (int) $product->stock > 0)->count();
        $outOfStockCount = $products->filter(fn ($product) => (int) $product->stock <= 0)->count();
        $lowStockCount = $products->filter(function ($product) use ($thresholdOverride) {
            $stock = (int) $product->stock;
            $threshold = $thresholdOverride > 0 ? $thresholdOverride : (int) $product->reorder_point;
            return $stock > 0 && $stock <= max(0, $threshold);
        })->count();
        $normalStockCount = max(0, $productsWithInventory - $lowStockCount);

        $inventoryTotalValue = $products->sum(function ($product) {
            $stock = max(0, (int) $product->stock);
            $unitCost = $this->safeNumericColumn($product, 'cost_price');
            if ($unitCost <= 0) {
                $unitCost = $this->safeNumericColumn($product, 'sale_price', (float) ($product->price ?? 0));
            }
            return $unitCost * $stock;
        });

        return response()->json([
            'message' => 'KPIs profesionales de inventario',
            'data' => [
                'products_with_inventory' => (int) $productsWithInventory,
                'normal_stock_products' => (int) $normalStockCount,
                'low_stock_products' => (int) $lowStockCount,
                'out_of_stock_products' => (int) $outOfStockCount,
                'inventory_total_value' => round((float) $inventoryTotalValue, 2),
                // Compatibilidad con frontend previo.
                'total_products' => (int) $products->count(),
                'inventory_value' => round((float) $inventoryTotalValue, 2),
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
            'message' => 'Movimientos de inventario',
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

    public function bulkDelete(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore();

        $validated = $request->validate([
            'all' => ['nullable', 'boolean'],
            'confirm' => ['nullable', 'string', 'max:50'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $deleteAll = (bool) ($validated['all'] ?? false);
        $selectedIds = collect($validated['ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if (!$deleteAll && $selectedIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No seleccionaste productos para eliminar.',
            ], 422);
        }

        if ($deleteAll) {
            $confirm = Str::upper(trim((string) ($validated['confirm'] ?? '')));
            if ($confirm !== 'ELIMINAR') {
                return response()->json([
                    'success' => false,
                    'message' => 'Confirmacion invalida. Escribe ELIMINAR para borrar todo el inventario.',
                ], 422);
            }
        }

        $query = Product::query()->where('store_id', (int) $store->id);
        if (!$deleteAll) {
            $query->whereIn('id', $selectedIds->all());
        }

        $total = (clone $query)->count();
        if ($total <= 0) {
            return response()->json([
                'success' => true,
                'deleted' => 0,
                'message' => 'No hay productos para eliminar.',
            ]);
        }

        DB::transaction(function () use ($query) {
            $query->delete();
        });

        return response()->json([
            'success' => true,
            'deleted' => (int) $total,
            'message' => $deleteAll
                ? 'Inventario eliminado correctamente.'
                : 'Productos seleccionados eliminados correctamente.',
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
            'message' => 'Movimientos de inventario',
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

    private function inventoryStatsSelectColumns(): array
    {
        $columns = ['id', 'stock', 'reorder_point', 'price'];

        foreach (['sale_price', 'cost_price', 'ref_adicional', 'unit', 'allow_backorder'] as $column) {
            if ($this->hasProductColumn($column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function safeNumericColumn(Product $product, string $column, float $fallback = 0.0): float
    {
        if (!$this->hasProductColumn($column)) {
            return $fallback;
        }

        return (float) ($product->{$column} ?? $fallback);
    }

    private function safeStringColumn(Product $product, string $column, string $fallback = ''): string
    {
        if (!$this->hasProductColumn($column)) {
            return $fallback;
        }

        $value = trim((string) ($product->{$column} ?? ''));
        return $value !== '' ? $value : $fallback;
    }

    private function hasProductColumn(string $column): bool
    {
        static $cache = [];

        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('products', $column);
        }

        return (bool) $cache[$column];
    }
}
