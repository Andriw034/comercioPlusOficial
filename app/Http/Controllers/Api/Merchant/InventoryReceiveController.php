<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryReceiveController extends Controller
{
    public function scanIn(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $payload = $request->validate([
            'code' => ['required', 'string', 'max:128'],
            'qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:80'],
            'reference' => ['nullable', 'string', 'max:191'],
            'request_id' => ['nullable', 'string', 'max:120'],
        ]);

        $code = trim((string) $payload['code']);
        $qty = (int) $payload['qty'];
        $reason = trim((string) ($payload['reason'] ?? 'purchase')) ?: 'purchase';
        $reference = trim((string) ($payload['reference'] ?? '')) ?: null;
        $requestId = trim((string) ($payload['request_id'] ?? '')) ?: null;

        if ($requestId) {
            $existing = InventoryMovement::query()
                ->where('store_id', (int) $store->id)
                ->where('request_id', $requestId)
                ->with('product')
                ->first();

            if ($existing && $existing->product) {
                return response()->json([
                    'message' => 'Solicitud ya procesada. No duplique el ingreso.',
                    'data' => [
                        'product' => $this->productPayload($existing->product->fresh()),
                        'movement' => $this->movementPayload($existing),
                        'idempotent' => true,
                    ],
                ]);
            }
        }

        $product = $this->findStoreProductByCode((int) $store->id, $code);
        if (! $product) {
            return response()->json([
                'message' => 'No encuentro ese codigo en tu catalogo. Quieres crear el producto?',
                'error_code' => 'PRODUCT_NOT_FOUND',
                'suggested_action' => 'CREATE_PRODUCT',
                'data' => [
                    'code' => $code,
                ],
            ], 404);
        }

        $actorId = (int) $request->user()->id;
        $movement = null;
        $updatedProduct = null;

        try {
            DB::transaction(function () use ($product, $qty, $reason, $reference, $requestId, $actorId, $store, &$movement, &$updatedProduct) {
                if ($requestId) {
                    $existing = InventoryMovement::query()
                        ->where('store_id', (int) $store->id)
                        ->where('request_id', $requestId)
                        ->with('product')
                        ->first();

                    if ($existing && $existing->product) {
                        $movement = $existing;
                        $updatedProduct = $existing->product->fresh();
                        return;
                    }
                }

                $lockedProduct = Product::query()
                    ->where('store_id', (int) $store->id)
                    ->where('id', (int) $product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockAfter = (int) $lockedProduct->stock + $qty;
                $lockedProduct->update(['stock' => $stockAfter]);

                $movement = InventoryMovement::query()->create([
                    'store_id' => (int) $store->id,
                    'product_id' => (int) $lockedProduct->id,
                    'type' => InventoryMovement::TYPE_PURCHASE,
                    'reason' => $reason,
                    'quantity' => $qty,
                    'stock_after' => $stockAfter,
                    'unit_cost' => (float) ($lockedProduct->cost_price ?? 0),
                    'unit_price' => (float) ($lockedProduct->price ?? 0),
                    'reference_type' => 'inventory_receive',
                    'reference_id' => null,
                    'reference' => $reference,
                    'request_id' => $requestId,
                    'note' => $this->buildMovementNote($reason, $reference),
                    'created_by' => $actorId,
                ]);

                $updatedProduct = $lockedProduct->fresh();
            });
        } catch (QueryException $e) {
            if ($requestId && $this->isDuplicateKeyError($e)) {
                $existing = InventoryMovement::query()
                    ->where('store_id', (int) $store->id)
                    ->where('request_id', $requestId)
                    ->with('product')
                    ->first();

                if ($existing && $existing->product) {
                    return response()->json([
                        'message' => 'Solicitud ya procesada. No duplique el ingreso.',
                        'data' => [
                            'product' => $this->productPayload($existing->product->fresh()),
                            'movement' => $this->movementPayload($existing),
                            'idempotent' => true,
                        ],
                    ]);
                }
            }

            throw $e;
        }

        return response()->json([
            'message' => "Listo, sume {$qty} unidades.",
            'data' => [
                'product' => $this->productPayload($updatedProduct),
                'movement' => $this->movementPayload($movement),
                'idempotent' => false,
            ],
        ]);
    }

    public function createFromScan(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $payload = $request->validate([
            'code' => ['required', 'string', 'max:128'],
            'code_type' => ['required', 'in:barcode,sku,qr'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'initial_qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:80'],
            'reference' => ['nullable', 'string', 'max:191'],
            'request_id' => ['nullable', 'string', 'max:120'],
        ]);

        $code = trim((string) $payload['code']);
        $codeType = (string) $payload['code_type'];
        $name = trim((string) $payload['name']);
        $price = isset($payload['price']) ? (float) $payload['price'] : 0.0;
        $initialQty = (int) $payload['initial_qty'];
        $reason = trim((string) ($payload['reason'] ?? 'purchase')) ?: 'purchase';
        $reference = trim((string) ($payload['reference'] ?? '')) ?: null;
        $requestId = trim((string) ($payload['request_id'] ?? '')) ?: null;

        if ($requestId) {
            $existing = InventoryMovement::query()
                ->where('store_id', (int) $store->id)
                ->where('request_id', $requestId)
                ->with('product')
                ->first();

            if ($existing && $existing->product) {
                return response()->json([
                    'message' => 'Solicitud ya procesada. No duplique la creacion.',
                    'data' => [
                        'product' => $this->productPayload($existing->product->fresh()),
                        'movement' => $this->movementPayload($existing),
                        'idempotent' => true,
                    ],
                ]);
            }
        }

        $existingCode = ProductCode::query()
            ->where('store_id', (int) $store->id)
            ->where('type', $codeType)
            ->where('value', $code)
            ->exists();

        if ($existingCode) {
            return response()->json([
                'message' => 'Ese codigo ya existe en tu inventario. Usa ingreso por escaner.',
                'error_code' => 'CODE_ALREADY_EXISTS',
            ], 422);
        }

        $categoryId = $this->resolveCategoryIdForStore(
            store: $store,
            requestedCategoryId: isset($payload['category_id']) ? (int) $payload['category_id'] : null,
        );

        $actorId = (int) $request->user()->id;
        $createdProduct = null;
        $movement = null;

        try {
            DB::transaction(function () use ($store, $actorId, $name, $price, $categoryId, $code, $codeType, $initialQty, $reason, $reference, $requestId, &$createdProduct, &$movement) {
                if ($requestId) {
                    $existing = InventoryMovement::query()
                        ->where('store_id', (int) $store->id)
                        ->where('request_id', $requestId)
                        ->with('product')
                        ->first();

                    if ($existing && $existing->product) {
                        $createdProduct = $existing->product->fresh();
                        $movement = $existing;
                        return;
                    }
                }

                $slug = $this->buildUniqueProductSlug((int) $store->id, $name);

                $createdProduct = Product::query()->create([
                    'name' => $name,
                    'description' => '',
                    'price' => $price,
                    'stock' => 0,
                    'category_id' => $categoryId,
                    'store_id' => (int) $store->id,
                    'user_id' => $actorId,
                    'status' => 1,
                    'slug' => $slug,
                ]);

                ProductCode::query()->create([
                    'product_id' => (int) $createdProduct->id,
                    'store_id' => (int) $store->id,
                    'type' => $codeType,
                    'value' => $code,
                    'is_primary' => true,
                ]);

                $lockedProduct = Product::query()
                    ->where('store_id', (int) $store->id)
                    ->where('id', (int) $createdProduct->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockAfter = (int) $lockedProduct->stock + $initialQty;
                $lockedProduct->update(['stock' => $stockAfter]);

                $movement = InventoryMovement::query()->create([
                    'store_id' => (int) $store->id,
                    'product_id' => (int) $lockedProduct->id,
                    'type' => InventoryMovement::TYPE_PURCHASE,
                    'reason' => $reason,
                    'quantity' => $initialQty,
                    'stock_after' => $stockAfter,
                    'unit_cost' => (float) ($lockedProduct->cost_price ?? 0),
                    'unit_price' => (float) ($lockedProduct->price ?? 0),
                    'reference_type' => 'inventory_create_from_scan',
                    'reference_id' => (int) $lockedProduct->id,
                    'reference' => $reference,
                    'request_id' => $requestId,
                    'note' => $this->buildMovementNote($reason, $reference),
                    'created_by' => $actorId,
                ]);

                $createdProduct = $lockedProduct->fresh();
            });
        } catch (QueryException $e) {
            if ($requestId && $this->isDuplicateKeyError($e)) {
                $existing = InventoryMovement::query()
                    ->where('store_id', (int) $store->id)
                    ->where('request_id', $requestId)
                    ->with('product')
                    ->first();

                if ($existing && $existing->product) {
                    return response()->json([
                        'message' => 'Solicitud ya procesada. No duplique la creacion.',
                        'data' => [
                            'product' => $this->productPayload($existing->product->fresh()),
                            'movement' => $this->movementPayload($existing),
                            'idempotent' => true,
                        ],
                    ]);
                }
            }

            throw $e;
        }

        return response()->json([
            'message' => "Listo. Cree el producto y sume {$initialQty} unidades.",
            'data' => [
                'product' => $this->productPayload($createdProduct),
                'movement' => $this->movementPayload($movement),
                'idempotent' => false,
            ],
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $limit = max(1, min(100, (int) $request->integer('limit', 50)));
        $query = InventoryMovement::query()
            ->where('store_id', (int) $store->id)
            ->with(['product:id,name,stock', 'creator:id,name'])
            ->orderByDesc('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->integer('product_id'));
        }

        if ($request->filled('type')) {
            $type = (string) $request->string('type');
            if (in_array($type, ['sale', 'purchase', 'adjustment', 'return', 'cancel'], true)) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', (string) $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', (string) $request->string('to'));
        }

        $movements = $query->limit($limit)->get();

        return response()->json([
            'message' => 'Movimientos de inventario',
            'data' => $movements->map(fn (InventoryMovement $movement) => $this->movementPayload($movement))->values(),
            'meta' => [
                'count' => $movements->count(),
                'limit' => $limit,
            ],
        ]);
    }

    private function resolveMerchantStore(Request $request): Store
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isMerchant') || ! $user->isMerchant()) {
            abort(403, 'Solo comerciantes pueden gestionar ingresos por escaner.');
        }

        $store = $user->store()->first();
        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
    }

    private function findStoreProductByCode(int $storeId, string $code): ?Product
    {
        $productCode = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('value', $code)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if ($productCode) {
            return Product::query()
                ->where('store_id', $storeId)
                ->where('id', (int) $productCode->product_id)
                ->first();
        }

        $hasBarcode = Schema::hasColumn('products', 'barcode');
        $hasSku = Schema::hasColumn('products', 'sku');
        $hasInternalCode = Schema::hasColumn('products', 'internal_code');

        if (! $hasBarcode && ! $hasSku && ! $hasInternalCode) {
            return null;
        }

        return Product::query()
            ->where('store_id', $storeId)
            ->where(function ($query) use ($code, $hasBarcode, $hasSku, $hasInternalCode) {
                if ($hasBarcode) {
                    $query->orWhere('barcode', $code);
                }
                if ($hasSku) {
                    $query->orWhere('sku', $code);
                }
                if ($hasInternalCode) {
                    $query->orWhere('internal_code', $code);
                }
            })
            ->first();
    }

    private function resolveCategoryIdForStore(Store $store, ?int $requestedCategoryId): int
    {
        $hasStoreId = Schema::hasColumn('categories', 'store_id');

        if ($requestedCategoryId) {
            $category = Category::query()->findOrFail($requestedCategoryId);
            if (
                $hasStoreId
                && $category->store_id !== null
                && (int) $category->store_id !== (int) $store->id
            ) {
                throw ValidationException::withMessages([
                    'category_id' => ['La categoria no pertenece a tu tienda.'],
                ]);
            }

            return (int) $category->id;
        }

        $query = Category::query();
        if ($hasStoreId) {
            $query->where('store_id', (int) $store->id);
        }

        $existing = $query->orderBy('id')->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $baseName = 'General';
        $slug = $this->buildUniqueCategorySlug($baseName);
        $category = Category::query()->create([
            'name' => $baseName,
            'slug' => $slug,
            'store_id' => $hasStoreId ? (int) $store->id : null,
            'description' => 'Categoria creada automaticamente para ingresos por escaner.',
        ]);

        return (int) $category->id;
    }

    private function buildUniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $counter = 1;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function buildUniqueProductSlug(int $storeId, string $name): string
    {
        $base = Str::slug($name) ?: 'producto';
        $slug = $base;
        $counter = 1;

        while (
            Product::query()
                ->where('store_id', $storeId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'stock' => (int) $product->stock,
            'price' => (float) ($product->price ?? 0),
            'slug' => (string) ($product->slug ?? ''),
        ];
    }

    private function movementPayload(InventoryMovement $movement): array
    {
        return [
            'id' => (int) $movement->id,
            'type' => (string) $movement->type,
            'reason' => (string) ($movement->reason ?? ''),
            'reference' => $movement->reference,
            'request_id' => $movement->request_id,
            'quantity' => (int) $movement->quantity,
            'stock_after' => (int) $movement->stock_after,
            'product_id' => (int) $movement->product_id,
            'product_name' => $movement->product?->name,
            'created_by' => $movement->creator?->name,
            'created_at' => $movement->created_at?->toIso8601String(),
        ];
    }

    private function buildMovementNote(string $reason, ?string $reference): string
    {
        if ($reference) {
            return "Ingreso por escaner. Motivo: {$reason}. Ref: {$reference}";
        }

        return "Ingreso por escaner. Motivo: {$reason}";
    }

    private function isDuplicateKeyError(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return $sqlState === '23000' || $driverCode === 1062;
    }
}

