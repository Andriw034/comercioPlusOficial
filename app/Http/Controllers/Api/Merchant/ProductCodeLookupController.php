<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductCodeLookupController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $payload = $request->validate([
            'code' => ['required', 'string', 'max:191'],
            'code_type' => ['nullable', 'in:barcode,qr,sku'],
        ]);

        $code = trim((string) $payload['code']);
        $codeType = isset($payload['code_type']) ? trim((string) $payload['code_type']) : null;

        $productCode = ProductCode::query()
            ->where('store_id', (int) $store->id)
            ->where('value', $code)
            ->when($codeType, fn ($query) => $query->where('type', $codeType))
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if ($productCode) {
            $product = Product::query()
                ->where('store_id', (int) $store->id)
                ->where('id', (int) $productCode->product_id)
                ->first();

            if ($product) {
                return response()->json([
                    'message' => 'Codigo encontrado en tu catalogo.',
                    'data' => [
                        'found' => true,
                        'source' => 'product_codes',
                        'code' => [
                            'type' => (string) $productCode->type,
                            'value' => (string) $productCode->value,
                            'is_primary' => (bool) $productCode->is_primary,
                        ],
                        'product' => $this->productPayload($product),
                    ],
                ]);
            }
        }

        $legacy = $this->findByLegacyCode((int) $store->id, $code, $codeType);
        if ($legacy) {
            return response()->json([
                'message' => 'Codigo encontrado en tu catalogo.',
                'data' => [
                    'found' => true,
                    'source' => 'legacy_column',
                    'code' => [
                        'type' => $legacy['type'],
                        'value' => $code,
                        'is_primary' => true,
                    ],
                    'product' => $this->productPayload($legacy['product']),
                ],
            ]);
        }

        return response()->json([
            'message' => 'No encuentro ese codigo en tu catalogo.',
            'error_code' => 'PRODUCT_NOT_FOUND',
            'suggested_action' => 'CREATE_PRODUCT',
            'data' => [
                'found' => false,
                'code' => $code,
                'code_type' => $codeType,
            ],
        ], 404);
    }

    private function resolveMerchantStore(Request $request): Store
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isMerchant') || ! $user->isMerchant()) {
            abort(403, 'Solo comerciantes pueden consultar codigos de producto.');
        }

        $store = $user->store()->first();
        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
    }

    private function findByLegacyCode(int $storeId, string $code, ?string $codeType): ?array
    {
        $hasBarcode = Schema::hasColumn('products', 'barcode');
        $hasSku = Schema::hasColumn('products', 'sku');
        $hasInternalCode = Schema::hasColumn('products', 'internal_code');

        if (! $hasBarcode && ! $hasSku && ! $hasInternalCode) {
            return null;
        }

        $query = Product::query()->where('store_id', $storeId);

        if ($codeType === 'barcode' && $hasBarcode) {
            $query->where('barcode', $code);
        } elseif ($codeType === 'sku' && $hasSku) {
            $query->where('sku', $code);
        } elseif ($codeType === 'qr' && $hasInternalCode) {
            $query->where('internal_code', $code);
        } else {
            $query->where(function ($nested) use ($code, $hasBarcode, $hasSku, $hasInternalCode) {
                if ($hasBarcode) {
                    $nested->orWhere('barcode', $code);
                }
                if ($hasSku) {
                    $nested->orWhere('sku', $code);
                }
                if ($hasInternalCode) {
                    $nested->orWhere('internal_code', $code);
                }
            });
        }

        $product = $query->first();
        if (! $product) {
            return null;
        }

        $resolvedType = 'barcode';
        if ($hasBarcode && (string) ($product->barcode ?? '') === $code) {
            $resolvedType = 'barcode';
        } elseif ($hasSku && (string) ($product->sku ?? '') === $code) {
            $resolvedType = 'sku';
        } elseif ($hasInternalCode && (string) ($product->internal_code ?? '') === $code) {
            $resolvedType = 'qr';
        }

        return [
            'type' => $resolvedType,
            'product' => $product,
        ];
    }

    private function productPayload(Product $product): array
    {
        $status = $product->status;
        if (is_numeric($status)) {
            $status = (int) $status === 1 ? 'active' : 'draft';
        }

        return [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'slug' => (string) ($product->slug ?? ''),
            'price' => (float) ($product->price ?? 0),
            'stock' => (int) ($product->stock ?? 0),
            'status' => (string) ($status ?? 'draft'),
            'category_id' => $product->category_id ? (int) $product->category_id : null,
            'image_url' => (string) ($product->image_url ?? $product->image ?? $product->image_path ?? ''),
        ];
    }
}

