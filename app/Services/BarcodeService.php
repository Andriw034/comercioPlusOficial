<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class BarcodeService
{
    public function ensurePrimaryBarcode(Product $product, int $storeId): ProductCode
    {
        if ((int) $product->store_id !== $storeId) {
            throw new RuntimeException('El producto no pertenece a la tienda autenticada.');
        }

        return DB::transaction(function () use ($product, $storeId) {
            $lockedProduct = Product::query()
                ->where('id', (int) $product->id)
                ->where('store_id', $storeId)
                ->lockForUpdate()
                ->firstOrFail();

            $primary = ProductCode::query()
                ->where('store_id', $storeId)
                ->where('product_id', (int) $lockedProduct->id)
                ->where('type', ProductCode::TYPE_BARCODE)
                ->where('is_primary', true)
                ->first();

            if ($primary) {
                return $primary;
            }

            $firstBarcode = ProductCode::query()
                ->where('store_id', $storeId)
                ->where('product_id', (int) $lockedProduct->id)
                ->where('type', ProductCode::TYPE_BARCODE)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->first();

            if ($firstBarcode) {
                ProductCode::query()
                    ->where('store_id', $storeId)
                    ->where('product_id', (int) $lockedProduct->id)
                    ->where('type', ProductCode::TYPE_BARCODE)
                    ->where('id', '!=', (int) $firstBarcode->id)
                    ->update(['is_primary' => false]);

                if (! $firstBarcode->is_primary) {
                    $firstBarcode->is_primary = true;
                    $firstBarcode->save();
                }

                return $firstBarcode->fresh();
            }

            $legacy = $this->legacyBarcodeValue($lockedProduct);
            if ($legacy !== null && $this->isBarcodeUnique($storeId, $legacy, (int) $lockedProduct->id)) {
                return ProductCode::query()->create([
                    'store_id' => $storeId,
                    'product_id' => (int) $lockedProduct->id,
                    'type' => ProductCode::TYPE_BARCODE,
                    'value' => $legacy,
                    'is_primary' => true,
                ]);
            }

            $generated = $this->generateUniqueBarcodeValue(
                storeId: $storeId,
                productId: (int) $lockedProduct->id,
            );

            return ProductCode::query()->create([
                'store_id' => $storeId,
                'product_id' => (int) $lockedProduct->id,
                'type' => ProductCode::TYPE_BARCODE,
                'value' => $generated,
                'is_primary' => true,
            ]);
        });
    }

    public function findByCode(int $storeId, string $code, ?string $type = ProductCode::TYPE_BARCODE): ?array
    {
        $normalizedCode = trim($code);
        if ($normalizedCode === '') {
            return null;
        }

        $productCode = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('value', $normalizedCode)
            ->when(
                $type,
                fn ($query) => $query->where('type', $type),
            )
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if ($productCode) {
            $product = Product::query()
                ->where('store_id', $storeId)
                ->where('id', (int) $productCode->product_id)
                ->first();

            if ($product) {
                return [
                    'source' => 'product_codes',
                    'code' => $productCode,
                    'product' => $product,
                ];
            }
        }

        $legacyProduct = $this->findByLegacyCode($storeId, $normalizedCode, $type);
        if ($legacyProduct) {
            return [
                'source' => 'legacy_column',
                'code' => null,
                'product' => $legacyProduct['product'],
                'resolved_type' => $legacyProduct['type'],
            ];
        }

        return null;
    }

    public function generateMissingBarcodesForStore(int $storeId, int $limit = 200): array
    {
        $safeLimit = max(1, min(1000, $limit));

        $products = Product::query()
            ->where('store_id', $storeId)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('product_codes')
                    ->whereColumn('product_codes.product_id', 'products.id')
                    ->where('product_codes.type', ProductCode::TYPE_BARCODE)
                    ->where('product_codes.is_primary', true);
            })
            ->orderBy('id')
            ->limit($safeLimit)
            ->get();

        $generated = [];
        foreach ($products as $product) {
            $code = $this->ensurePrimaryBarcode($product, $storeId);
            $generated[] = [
                'product_id' => (int) $product->id,
                'code' => (string) $code->value,
            ];
        }

        return [
            'generated_count' => count($generated),
            'generated' => $generated,
            'processed_limit' => $safeLimit,
        ];
    }

    public function renderBarcode(string $value, string $format = 'png'): array
    {
        $normalizedValue = trim($value);
        if ($normalizedValue === '') {
            throw new RuntimeException('Codigo de barras vacio.');
        }

        $normalizedFormat = strtolower(trim($format));
        if (! in_array($normalizedFormat, ['png', 'svg'], true)) {
            throw new RuntimeException('Formato no soportado. Usa png o svg.');
        }

        if ($normalizedFormat === 'svg') {
            if (! class_exists(\Picqer\Barcode\BarcodeGeneratorSVG::class)) {
                throw new RuntimeException('Falta dependencia picqer/php-barcode-generator para generar SVG.');
            }

            $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
            $content = $generator->getBarcode(
                $normalizedValue,
                \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128
            );

            return [
                'mime' => 'image/svg+xml',
                'content' => $content,
                'extension' => 'svg',
            ];
        }

        if (! class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
            throw new RuntimeException('Falta dependencia picqer/php-barcode-generator para generar PNG.');
        }

        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $content = $generator->getBarcode(
            $normalizedValue,
            \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128,
            2,
            60
        );

        return [
            'mime' => 'image/png',
            'content' => $content,
            'extension' => 'png',
        ];
    }

    private function legacyBarcodeValue(Product $product): ?string
    {
        if (! Schema::hasColumn('products', 'barcode')) {
            return null;
        }

        $value = trim((string) ($product->getAttribute('barcode') ?? ''));
        return $value !== '' ? $value : null;
    }

    private function isBarcodeUnique(int $storeId, string $value, ?int $excludeProductId = null): bool
    {
        $query = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->where('value', $value);

        if ($excludeProductId !== null) {
            $query->where('product_id', '!=', $excludeProductId);
        }

        return ! $query->exists();
    }

    private function generateUniqueBarcodeValue(int $storeId, int $productId): string
    {
        $storePart = str_pad(substr((string) $storeId, -4), 4, '0', STR_PAD_LEFT);
        $productPart = str_pad(substr((string) $productId, -8), 8, '0', STR_PAD_LEFT);

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $suffix = str_pad((string) $attempt, 2, '0', STR_PAD_LEFT);
            $candidate = "CP{$storePart}{$productPart}{$suffix}";

            if ($this->isBarcodeUnique($storeId, $candidate, $productId)) {
                return $candidate;
            }
        }

        throw new RuntimeException('No pude generar un codigo de barras unico para el producto.');
    }

    private function findByLegacyCode(int $storeId, string $code, ?string $requestedType): ?array
    {
        $hasBarcode = Schema::hasColumn('products', 'barcode');
        $hasSku = Schema::hasColumn('products', 'sku');
        $hasInternalCode = Schema::hasColumn('products', 'internal_code');

        if (! $hasBarcode && ! $hasSku && ! $hasInternalCode) {
            return null;
        }

        $query = Product::query()->where('store_id', $storeId);

        if ($requestedType === 'barcode' && $hasBarcode) {
            $query->where('barcode', $code);
        } elseif ($requestedType === 'sku' && $hasSku) {
            $query->where('sku', $code);
        } elseif ($requestedType === 'qr' && $hasInternalCode) {
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

        $resolvedType = ProductCode::TYPE_BARCODE;
        if ($hasBarcode && (string) ($product->barcode ?? '') === $code) {
            $resolvedType = ProductCode::TYPE_BARCODE;
        } elseif ($hasSku && (string) ($product->sku ?? '') === $code) {
            $resolvedType = ProductCode::TYPE_SKU;
        } elseif ($hasInternalCode && (string) ($product->internal_code ?? '') === $code) {
            $resolvedType = ProductCode::TYPE_QR;
        }

        return [
            'type' => $resolvedType,
            'product' => $product,
        ];
    }
}
