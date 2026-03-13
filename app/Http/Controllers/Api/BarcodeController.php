<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BarcodeController extends Controller
{
    public function __construct(
        private readonly BarcodeService $barcodeService
    ) {}

    public function show(Request $request, Product $product)
    {
        $store = $this->resolveMerchantStore($request);
        if ((int) $product->store_id !== (int) $store->id) {
            return response()->json([
                'message' => 'Producto no encontrado en tu tienda.',
            ], 404);
        }

        $code = $this->barcodeService->ensurePrimaryBarcode($product, (int) $store->id);
        $format = strtolower(trim((string) $request->query('format', 'json')));

        if (in_array($format, ['png', 'svg'], true)) {
            try {
                $rendered = $this->barcodeService->renderBarcode((string) $code->value, $format);

                return response($rendered['content'], 200)
                    ->header('Content-Type', $rendered['mime'])
                    ->header('Content-Disposition', 'inline; filename="barcode-' . $product->id . '.' . $rendered['extension'] . '"');
            } catch (RuntimeException $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_code' => 'BARCODE_RENDERING_UNAVAILABLE',
                ], 501);
            }
        }

        return response()->json([
            'message' => 'Codigo de barras listo.',
            'data' => [
                'product_id' => (int) $product->id,
                'code' => (string) $code->value,
                'type' => (string) $code->type,
                'is_primary' => (bool) $code->is_primary,
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:191'],
            'code_type' => ['nullable', 'in:barcode,qr,sku'],
        ]);

        $found = $this->barcodeService->findByCode(
            storeId: (int) $store->id,
            code: (string) $validated['code'],
            type: isset($validated['code_type']) ? (string) $validated['code_type'] : ProductCode::TYPE_BARCODE,
        );

        if (! $found) {
            return response()->json([
                'message' => 'No encuentro ese codigo en tu catalogo.',
                'error_code' => 'PRODUCT_NOT_FOUND',
                'suggested_action' => 'CREATE_PRODUCT',
                'data' => [
                    'found' => false,
                    'code' => (string) $validated['code'],
                    'code_type' => isset($validated['code_type']) ? (string) $validated['code_type'] : ProductCode::TYPE_BARCODE,
                ],
            ], 404);
        }

        /** @var Product $product */
        $product = $found['product'];
        /** @var ProductCode|null $code */
        $code = $found['code'] ?? null;
        $resolvedType = $code?->type ?? ($found['resolved_type'] ?? ProductCode::TYPE_BARCODE);
        $resolvedValue = $code?->value ?? (string) $validated['code'];
        $isPrimary = $code ? (bool) $code->is_primary : true;

        return response()->json([
            'message' => 'Codigo encontrado en tu catalogo.',
            'data' => [
                'found' => true,
                'source' => (string) ($found['source'] ?? 'product_codes'),
                'code' => [
                    'type' => $resolvedType,
                    'value' => $resolvedValue,
                    'is_primary' => $isPrimary,
                ],
                'product' => $this->productPayload($product),
            ],
        ]);
    }

    public function publicSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:191'],
            'code_type' => ['nullable', 'in:barcode,qr,sku'],
        ]);

        $requestedCode = trim((string) $validated['code']);
        $normalizedCode = mb_strtolower($requestedCode);
        $requestedType = isset($validated['code_type']) ? (string) $validated['code_type'] : null;

        $product = Product::query()
            ->with(['store', 'category', 'productCodes'])
            ->whereHas('store', fn ($query) => $query->where('is_visible', true))
            ->where(function ($query) use ($normalizedCode, $requestedType) {
                $query->whereHas('productCodes', function ($codeQuery) use ($normalizedCode, $requestedType) {
                    $codeQuery->whereRaw('LOWER(value) = ?', [$normalizedCode]);
                    if ($requestedType) {
                        $codeQuery->where('type', $requestedType);
                    }
                });

                if (!$requestedType || $requestedType === ProductCode::TYPE_SKU) {
                    $query->orWhereRaw('LOWER(sku) = ?', [$normalizedCode]);
                }
            })
            ->latest('id')
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'No encontre ese codigo en el catalogo publico.',
                'error_code' => 'PRODUCT_NOT_FOUND',
                'data' => [
                    'found' => false,
                    'code' => $requestedCode,
                ],
            ], 404);
        }

        /** @var ProductCode|null $matchedCode */
        $matchedCode = $product->productCodes
            ->first(function (ProductCode $code) use ($normalizedCode, $requestedType) {
                $valueMatches = mb_strtolower((string) $code->value) === $normalizedCode;
                $typeMatches = !$requestedType || $code->type === $requestedType;
                return $valueMatches && $typeMatches;
            });

        $resolvedType = $matchedCode?->type ?? ProductCode::TYPE_SKU;
        $resolvedValue = $matchedCode?->value ?? (string) ($product->sku ?: $requestedCode);
        $isPrimary = $matchedCode ? (bool) $matchedCode->is_primary : true;

        return response()->json([
            'message' => 'Codigo encontrado.',
            'data' => [
                'found' => true,
                'source' => $matchedCode ? 'product_codes' : 'sku',
                'code' => [
                    'type' => $resolvedType,
                    'value' => $resolvedValue,
                    'is_primary' => $isPrimary,
                ],
                'product' => $this->publicProductPayload($product),
            ],
        ]);
    }

    public function generateBatch(Request $request): JsonResponse
    {
        $store = $this->resolveMerchantStore($request);

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $result = $this->barcodeService->generateMissingBarcodesForStore(
            storeId: (int) $store->id,
            limit: isset($validated['limit']) ? (int) $validated['limit'] : 200,
        );

        return response()->json([
            'message' => 'Generacion de codigos finalizada.',
            'data' => $result,
        ]);
    }

    private function resolveMerchantStore(Request $request): Store
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isMerchant') || ! $user->isMerchant()) {
            abort(403, 'Solo comerciantes pueden gestionar codigos de barras.');
        }

        $store = $user->store()->first();
        if (! $store) {
            abort(404, 'Tienda no encontrada para este usuario.');
        }

        return $store;
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

    private function publicProductPayload(Product $product): array
    {
        $payload = $this->productPayload($product);

        $payload['store'] = $product->store ? [
            'id' => (int) $product->store->id,
            'name' => (string) $product->store->name,
            'slug' => (string) ($product->store->slug ?? ''),
            'logo_url' => (string) ($product->store->logo_url ?? $product->store->logo ?? ''),
            'cover_url' => (string) ($product->store->cover_url ?? $product->store->background_url ?? $product->store->cover ?? ''),
        ] : null;

        $payload['category'] = $product->category ? [
            'id' => (int) $product->category->id,
            'name' => (string) $product->category->name,
        ] : null;

        return $payload;
    }
}
