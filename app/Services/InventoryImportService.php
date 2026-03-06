<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Models\StoreCounter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InventoryImportService
{
    private const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;
    private const PLACEHOLDER_IMAGE_URL = '/placeholder-product.png';
    private const STANDARD_FIELDS = [
        'name',
        'sku',
        'barcode',
        'description',
        'price',
        'cost_price',
        'sale_price',
        'stock',
        'reorder_point',
        'unit',
        'ref_adicional',
        'category',
        'brand',
        'image_url',
    ];
    private const STANDARD_FIELD_ALIASES = [
        'name' => ['name', 'nombre', 'producto', 'product', 'product_name', 'articulo', 'item'],
        'sku' => ['sku', 'codigo', 'codigo_sku', 'codigo_de_sku', 'product_code', 'code', 'codigosku', 'id_producto', 'referencia'],
        'barcode' => ['barcode', 'codigo_barras', 'codigo_de_barras', 'codigobarras', 'ean', 'ean13', 'gtin', 'upc'],
        'description' => ['description', 'descripcion', 'detalle', 'detalles'],
        'price' => ['price', 'precio', 'valor'],
        'cost_price' => ['v_compra', 'v/compra', 'vcompra', 'valor_compra', 'costo', 'costo_unitario', 'cost_price'],
        'sale_price' => ['p_publico', 'p/publico', 'ppublico', 'precio_venta', 'sale_price', 'valor_venta'],
        'stock' => ['stock', 'inventario', 'existencias', 'cantidad', 'qty', 'quantity'],
        'reorder_point' => ['stock_minimo', 'minimo', 'stock_min', 'punto_reorden', 'reorder_point'],
        'unit' => ['unidad', 'unit', 'u', 'medida'],
        'ref_adicional' => ['ref_adicional', 'ref_adic', 'referencia_adicional', 'referencia_extra', 'variante'],
        'category' => ['category', 'categoria', 'cat', 'linea', 'cod_grupo', 'grupo'],
        'brand' => ['brand', 'marca', 'fabricante'],
        'image_url' => ['image_url', 'image', 'imagen', 'foto', 'image_link', 'imagen_url', 'url_imagen'],
    ];

    public function preview(Store $store, UploadedFile $file): array
    {
        $this->validateFile($file);

        $rows = $this->parseFile($file);
        if ($rows->isEmpty()) {
            return [
                'headers' => [],
                'preview_rows' => [],
                'analysis_rows' => [],
                'total_rows' => 0,
            ];
        }

        $headers = $rows->first();
        $headerKeys = $headers->map(fn ($header) => $this->canonicalHeaderKey((string) $header))->values();
        $analysisRows = [];
        $counterCursor = $this->getStoreNextBarcodeCounter((int) $store->id);

        foreach ($rows->slice(1, 10)->values() as $offset => $row) {
            $rowNumber = $offset + 2;
            $normalizedRow = collect($row)->map(fn ($value) => $this->normalizeCell($value))->values();

            if ($this->isRowEmpty($normalizedRow)) {
                $analysisRows[] = [
                    'row' => $rowNumber,
                    'matched_by' => 'none',
                    'action' => 'skip',
                    'resolved_sku' => null,
                    'resolved_barcode' => null,
                    'barcode_source' => 'none',
                ];
                continue;
            }

            [$standard] = $this->mapRowToData($normalizedRow, $headers, $headerKeys);
            $incomingName = trim((string) ($standard['name'] ?? ''));
            $incomingSku = $this->sanitizeOptionalIdentifier($standard['sku'] ?? null);
            $incomingBarcode = $this->sanitizeOptionalIdentifier($standard['barcode'] ?? null);

            [$matchedProduct, $matchedBy] = $this->findMatchingProduct(
                storeId: (int) $store->id,
                barcode: $incomingBarcode,
                sku: $incomingSku,
                name: $incomingName,
            );

            $resolvedSku = $incomingSku;
            if ($resolvedSku === null && $matchedProduct) {
                $resolvedSku = $this->sanitizeOptionalIdentifier($matchedProduct->sku ?? null);
            }

            if ($incomingBarcode !== null) {
                $resolvedBarcode = $incomingBarcode;
                $barcodeSource = 'excel';
            } else {
                $existingPrimary = $matchedProduct
                    ? $this->findPrimaryBarcodeForProduct((int) $store->id, (int) $matchedProduct->id)
                    : null;
                if ($existingPrimary !== null) {
                    $resolvedBarcode = $existingPrimary;
                    $barcodeSource = 'existing';
                } else {
                    $resolvedBarcode = $this->previewGeneratedBarcode((int) $store->id, $counterCursor);
                    $barcodeSource = 'generated';
                }
            }

            $analysisRows[] = [
                'row' => $rowNumber,
                'matched_by' => $matchedBy,
                'action' => $matchedProduct ? 'update' : 'create',
                'resolved_sku' => $resolvedSku,
                'resolved_barcode' => $resolvedBarcode,
                'barcode_source' => $barcodeSource,
            ];
        }

        return [
            'headers' => $headers,
            'preview_rows' => $rows->slice(1, 10)->values()->all(),
            'analysis_rows' => $analysisRows,
            'total_rows' => max(0, $rows->count() - 1),
        ];
    }

    public function generateTemplateCsv(Store $store): string
    {
        $headers = [
            'CODIGO',
            'BARCODE',
            'PRODUCTO',
            'UNIDAD',
            'REF ADICIONAL',
            'CANTIDAD',
            'V/COMPRA',
            'P/PUBLICO',
            'COD GRUPO',
            'DESCRIPCION',
            'MARCA',
        ];

        $headers = array_values(array_unique(array_merge(
            $headers,
            $this->getStoreMetadataColumns($store),
        )));

        $example = [
            'SKU-001',
            '7701234500012',
            'Abrasadera metalica',
            'UNID',
            '25MM',
            '12',
            '1200',
            '1800',
            'General',
            'Repuesto de ejemplo',
            'Generica',
        ];
        $example = array_pad($example, count($headers), '');

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('No se pudo generar el template CSV.');
        }

        fputcsv($stream, $headers);
        fputcsv($stream, $example);

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        // BOM UTF-8 for better compatibility with Excel.
        return "\xEF\xBB\xBF" . $csv;
    }

    public function import(Store $store, UploadedFile $file, bool $upsert = true): array
    {
        $this->validateFile($file);

        $rows = $this->parseFile($file);
        if ($rows->count() < 2) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => [['row' => 1, 'error' => 'Archivo vacio o sin filas de datos.']],
            ];
        }

        $headers = $rows->first()->map(fn ($header) => $this->normalizeCell($header))->values();
        $headerKeys = $headers->map(fn ($header) => $this->canonicalHeaderKey((string) $header))->values();

        $result = [
            'success' => true,
            'imported' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];
        $defaultCategory = null;

        DB::beginTransaction();
        try {
            // In strict mode (upsert disabled), duplicated SKUs inside the same file are rejected.
            if (!$upsert) {
                $duplicates = $this->duplicateSkusInFile($rows, $headerKeys);
                if ($duplicates !== []) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'imported' => 0,
                        'updated' => 0,
                        'failed' => count($duplicates),
                        'errors' => [[
                            'row' => 1,
                            'error' => 'SKUs duplicados en archivo: ' . implode(', ', $duplicates),
                        ]],
                    ];
                }
            }

            foreach ($rows->slice(1)->values() as $offset => $row) {
                $rowNumber = $offset + 2;

                try {
                    $row = collect($row)->map(fn ($value) => $this->normalizeCell($value))->values();
                    if ($this->isRowEmpty($row)) {
                        continue;
                    }

                    [$standard, $metadata] = $this->mapRowToData($row, $headers, $headerKeys);
                    $name = trim((string) ($standard['name'] ?? ''));
                    $sku = $this->sanitizeOptionalIdentifier($standard['sku'] ?? null);
                    $barcode = $this->sanitizeOptionalIdentifier($standard['barcode'] ?? null);

                    if ($name === '' && $sku === null && $barcode === null) {
                        continue;
                    }

                    if ($name === '') {
                        $name = 'Producto sin nombre';
                    }

                    if (isset($standard['category']) && $standard['category'] !== '') {
                        $category = $this->findOrCreateCategory((string) $standard['category'], $store);
                        $standard['category_id'] = (int) $category->id;
                    } else {
                        $defaultCategory ??= $this->findOrCreateCategory('Sin categoria', $store);
                        $standard['category_id'] = (int) $defaultCategory->id;
                    }
                    unset($standard['category']);

                    $standard['cost_price'] = $this->toMoney($standard['cost_price'] ?? 0);
                    $standard['sale_price'] = $this->toMoney($standard['sale_price'] ?? ($standard['price'] ?? 0));
                    $standard['price'] = $this->toMoney($standard['price'] ?? $standard['sale_price']);
                    if ((float) $standard['price'] <= 0 && (float) $standard['sale_price'] > 0) {
                        $standard['price'] = (float) $standard['sale_price'];
                    }
                    if ((float) $standard['sale_price'] <= 0 && (float) $standard['price'] > 0) {
                        $standard['sale_price'] = (float) $standard['price'];
                    }

                    $standard['stock'] = $this->toInt($standard['stock'] ?? 0);
                    $standard['reorder_point'] = $this->toInt($standard['reorder_point'] ?? 0);
                    $standard['status'] = 1;
                    $standard['name'] = $name;
                    $standard['sku'] = $sku;
                    $standard['description'] = (string) ($standard['description'] ?? '');
                    $standard['brand'] = (string) ($standard['brand'] ?? '');
                    $standard['unit'] = strtoupper(trim((string) ($standard['unit'] ?? 'UND')));
                    if ($standard['unit'] === '') {
                        $standard['unit'] = 'UND';
                    }
                    $standard['ref_adicional'] = trim((string) ($standard['ref_adicional'] ?? ''));

                    $incomingImage = (string) ($standard['image_url'] ?? '');
                    $standard['image_url'] = $incomingImage !== ''
                        ? $this->resolveImageUrl($incomingImage, (int) $store->id)
                        : self::PLACEHOLDER_IMAGE_URL;

                    [$product, $matchedBy] = $this->findMatchingProduct(
                        storeId: (int) $store->id,
                        barcode: $barcode,
                        sku: $sku,
                        name: $name,
                    );

                    if ($product && !$upsert) {
                        throw new \RuntimeException("Producto existente por {$matchedBy} (upsert deshabilitado).");
                    }

                    if ($product) {
                        if ($sku !== null) {
                            $this->assertSkuAvailableForProduct((int) $store->id, $sku, (int) $product->id);
                        }

                        $product->price = $standard['price'];
                        $product->stock = $standard['stock'];
                        $product->name = $standard['name'];
                        $product->description = $standard['description'];
                        $product->brand = $standard['brand'];
                        $product->cost_price = $standard['cost_price'];
                        $product->sale_price = $standard['sale_price'];
                        $product->reorder_point = $standard['reorder_point'];
                        $product->unit = $standard['unit'];
                        $product->ref_adicional = $standard['ref_adicional'];
                        $product->category_id = (int) $standard['category_id'];
                        if ($sku !== null) {
                            $product->sku = $sku;
                        }
                        if ($incomingImage !== '') {
                            $product->image_url = $standard['image_url'];
                        }

                        $currentMeta = is_array($product->metadata) ? $product->metadata : [];
                        $product->metadata = array_merge($currentMeta, $metadata);
                        $product->save();

                        $this->ensurePrimaryBarcodeForImportedProduct(
                            storeId: (int) $store->id,
                            product: $product,
                            incomingBarcode: $barcode,
                        );

                        $result['updated']++;
                    } else {
                        if ($sku !== null) {
                            $this->assertSkuAvailableForProduct((int) $store->id, $sku, null);
                        }

                        $payload = array_merge($standard, [
                            'store_id' => (int) $store->id,
                            'user_id' => (int) $store->user_id,
                            'name' => (string) ($standard['name'] ?? 'Producto importado'),
                            'description' => (string) ($standard['description'] ?? ''),
                            'brand' => (string) ($standard['brand'] ?? ''),
                            'sku' => $sku,
                            'slug' => $this->generateUniqueProductSlug($name, $sku ?? ''),
                            'metadata' => $metadata,
                        ]);

                        /** @var Product $created */
                        $created = Product::query()->create($payload);
                        $this->ensurePrimaryBarcodeForImportedProduct(
                            storeId: (int) $store->id,
                            product: $created,
                            incomingBarcode: $barcode,
                        );
                        $result['imported']++;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Inventory import row failed', [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                    ]);
                    $result['failed']++;
                    $result['errors'][] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Inventory import failed', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => 1,
                'errors' => [['row' => 1, 'error' => $e->getMessage()]],
            ];
        }

        if (($result['imported'] + $result['updated']) === 0 && $result['failed'] > 0) {
            $result['success'] = false;
        }

        return $result;
    }

    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('Archivo invalido.');
        }

        if ((int) $file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new \RuntimeException('Archivo mayor a 10MB.');
        }

        $ext = Str::lower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
            throw new \RuntimeException('Formato no soportado. Use CSV o XLSX.');
        }
    }

    private function parseFile(UploadedFile $file): Collection
    {
        $ext = Str::lower((string) $file->getClientOriginalExtension());
        return $ext === 'csv' ? $this->parseCsv($file) : $this->parseSpreadsheet($file);
    }

    private function parseCsv(UploadedFile $file): Collection
    {
        $rows = collect();
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $sampleLine = fgets($handle);
        if ($sampleLine === false) {
            fclose($handle);
            return $rows;
        }

        rewind($handle);
        $delimiter = $this->detectCsvDelimiter($sampleLine);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows->push(collect($data)->map(fn ($value) => $this->normalizeCell($value))->values());
        }
        fclose($handle);

        return $rows;
    }

    private function parseSpreadsheet(UploadedFile $file): Collection
    {
        $rows = collect();
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowValues = collect();
            foreach ($cellIterator as $cell) {
                $rowValues->push($this->normalizeCell((string) $cell->getFormattedValue()));
            }
            $rows->push($rowValues->values());
        }

        return $rows;
    }

    private function duplicateSkusInFile(Collection $rows, Collection $headerKeys): array
    {
        $skuIndex = $headerKeys->search('sku');
        if ($skuIndex === false) {
            return [];
        }

        $skus = $rows->slice(1)->map(function ($row) use ($skuIndex) {
            return Str::upper(trim((string) ($row[$skuIndex] ?? '')));
        })->filter()->values();

        return $skus->duplicates()->unique()->values()->all();
    }

    private function sanitizeOptionalIdentifier(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));
        return $normalized !== '' ? $normalized : null;
    }

    private function findMatchingProduct(int $storeId, ?string $barcode, ?string $sku, string $name): array
    {
        if ($barcode !== null) {
            $product = $this->findProductByBarcode($storeId, $barcode);
            if ($product) {
                return [$product, 'barcode'];
            }
        }

        if ($sku !== null) {
            $product = $this->findProductBySku($storeId, $sku);
            if ($product) {
                return [$product, 'sku'];
            }
        }

        if ($barcode === null && $sku === null) {
            $product = $this->findProductByNormalizedName($storeId, $name);
            if ($product) {
                return [$product, 'name'];
            }
        }

        return [null, 'none'];
    }

    private function findProductByBarcode(int $storeId, string $barcode): ?Product
    {
        $code = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->where('value', $barcode)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if (! $code) {
            return null;
        }

        return Product::query()
            ->where('store_id', $storeId)
            ->where('id', (int) $code->product_id)
            ->first();
    }

    private function findProductBySku(int $storeId, string $sku): ?Product
    {
        return Product::query()
            ->where('store_id', $storeId)
            ->where('sku', $sku)
            ->first();
    }

    private function findProductByNormalizedName(int $storeId, string $name): ?Product
    {
        $normalizedName = $this->normalizeNameToken($name);
        if ($normalizedName === '') {
            return null;
        }

        return Product::query()
            ->where('store_id', $storeId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();
    }

    private function normalizeNameToken(string $name): string
    {
        return Str::lower(trim($name));
    }

    private function assertSkuAvailableForProduct(int $storeId, string $sku, ?int $currentProductId): void
    {
        $query = Product::query()
            ->where('store_id', $storeId)
            ->where('sku', $sku);

        if ($currentProductId !== null) {
            $query->where('id', '!=', $currentProductId);
        }

        $exists = $query->exists();

        if ($exists) {
            throw new \RuntimeException("SKU {$sku} ya existe en otro producto.");
        }
    }

    private function ensurePrimaryBarcodeForImportedProduct(int $storeId, Product $product, ?string $incomingBarcode): string
    {
        if ($incomingBarcode !== null) {
            return $this->ensureIncomingBarcodeAsPrimary($storeId, $product, $incomingBarcode);
        }

        $existingPrimary = $this->findPrimaryBarcodeForProduct($storeId, (int) $product->id);
        if ($existingPrimary !== null) {
            return $existingPrimary;
        }

        $fallback = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('product_id', (int) $product->id)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->orderBy('id')
            ->first();

        if ($fallback) {
            ProductCode::query()
                ->where('store_id', $storeId)
                ->where('product_id', (int) $product->id)
                ->where('type', ProductCode::TYPE_BARCODE)
                ->update(['is_primary' => false]);

            $fallback->is_primary = true;
            $fallback->save();

            return (string) $fallback->value;
        }

        $generated = $this->generateNextInternalBarcode($storeId);

        ProductCode::query()->create([
            'store_id' => $storeId,
            'product_id' => (int) $product->id,
            'type' => ProductCode::TYPE_BARCODE,
            'value' => $generated,
            'is_primary' => true,
        ]);

        return $generated;
    }

    private function ensureIncomingBarcodeAsPrimary(int $storeId, Product $product, string $incomingBarcode): string
    {
        $existingCode = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->where('value', $incomingBarcode)
            ->first();

        if ($existingCode && (int) $existingCode->product_id !== (int) $product->id) {
            throw new \RuntimeException("El barcode {$incomingBarcode} ya pertenece a otro producto.");
        }

        ProductCode::query()
            ->where('store_id', $storeId)
            ->where('product_id', (int) $product->id)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->update(['is_primary' => false]);

        if (! $existingCode) {
            ProductCode::query()->create([
                'store_id' => $storeId,
                'product_id' => (int) $product->id,
                'type' => ProductCode::TYPE_BARCODE,
                'value' => $incomingBarcode,
                'is_primary' => true,
            ]);

            return $incomingBarcode;
        }

        ProductCode::query()
            ->where('id', (int) $existingCode->id)
            ->update([
                'product_id' => (int) $product->id,
                'is_primary' => true,
            ]);

        return (string) $existingCode->value;
    }

    private function findPrimaryBarcodeForProduct(int $storeId, int $productId): ?string
    {
        $code = ProductCode::query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('type', ProductCode::TYPE_BARCODE)
            ->where('is_primary', true)
            ->first();

        return $code ? (string) $code->value : null;
    }

    private function previewGeneratedBarcode(int $storeId, int &$counterCursor): string
    {
        $candidate = '';
        do {
            $candidate = sprintf('CP-%06d', $counterCursor);
            $counterCursor++;
        } while (
            ProductCode::query()
                ->where('store_id', $storeId)
                ->where('type', ProductCode::TYPE_BARCODE)
                ->where('value', $candidate)
                ->exists()
        );

        return $candidate;
    }

    private function getStoreNextBarcodeCounter(int $storeId): int
    {
        $counter = StoreCounter::query()
            ->where('store_id', $storeId)
            ->first();

        if (! $counter) {
            return 1;
        }

        return max(1, (int) $counter->next_product_barcode);
    }

    private function generateNextInternalBarcode(int $storeId): string
    {
        $counter = StoreCounter::query()
            ->where('store_id', $storeId)
            ->lockForUpdate()
            ->first();

        if (! $counter) {
            StoreCounter::query()->create([
                'store_id' => $storeId,
                'next_product_barcode' => 1,
            ]);

            $counter = StoreCounter::query()
                ->where('store_id', $storeId)
                ->lockForUpdate()
                ->firstOrFail();
        }

        while (true) {
            $candidate = sprintf('CP-%06d', max(1, (int) $counter->next_product_barcode));
            $counter->next_product_barcode = max(1, (int) $counter->next_product_barcode) + 1;
            $counter->save();

            $alreadyExists = ProductCode::query()
                ->where('store_id', $storeId)
                ->where('type', ProductCode::TYPE_BARCODE)
                ->where('value', $candidate)
                ->exists();

            if (! $alreadyExists) {
                return $candidate;
            }
        }
    }

    private function mapRowToData(Collection $row, Collection $headers, Collection $headerKeys): array
    {
        $standard = [];
        $metadata = [];

        foreach ($headers as $index => $originalHeader) {
            $value = $this->normalizeCell($row[$index] ?? '');
            if ($value === '') {
                continue;
            }

            $normalized = (string) ($headerKeys[$index] ?? '');
            if (in_array($normalized, self::STANDARD_FIELDS, true)) {
                $standard[$normalized] = $value;
            } else {
                $metadata[(string) $originalHeader] = $value;
            }
        }

        return [$standard, $metadata];
    }

    private function findOrCreateCategory(string $categoryName, Store $store): Category
    {
        $categoryName = trim($categoryName);

        $existing = Category::query()
            ->where('store_id', (int) $store->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return Category::query()->create([
            'name' => $categoryName,
            'slug' => Str::slug($categoryName) ?: ('categoria-' . Str::random(6)),
            'store_id' => (int) $store->id,
        ]);
    }

    private function resolveImageUrl(string $imageUrl, int $storeId): string
    {
        if ($imageUrl === '' || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return self::PLACEHOLDER_IMAGE_URL;
        }

        try {
            $response = Http::timeout(8)->get($imageUrl);
            if (!$response->successful()) {
                return self::PLACEHOLDER_IMAGE_URL;
            }

            $contentType = (string) $response->header('Content-Type');
            if ($contentType !== '' && !Str::startsWith(Str::lower($contentType), 'image/')) {
                return self::PLACEHOLDER_IMAGE_URL;
            }

            $ext = Str::afterLast(parse_url($imageUrl, PHP_URL_PATH) ?? '', '.');
            $ext = Str::lower($ext);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)) {
                $ext = 'jpg';
            }

            $path = "products/import/{$storeId}/" . Str::uuid() . ".{$ext}";
            Storage::disk('public')->put($path, $response->body());

            return Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            Log::info('Inventory import image download failed', ['message' => $e->getMessage()]);
            return self::PLACEHOLDER_IMAGE_URL;
        }
    }

    private function toMoney(mixed $value): float
    {
        return round(max(0, $this->parseNumber($value)), 2);
    }

    private function toInt(mixed $value): int
    {
        return max(0, (int) round($this->parseNumber($value)));
    }

    private function parseNumber(mixed $value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $normalized = str_replace(["\xC2\xA0", ' '], '', $raw);
        $normalized = preg_replace('/[^0-9,\.\-]/', '', $normalized) ?? '';
        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            return 0.0;
        }

        $lastDot = strrpos($normalized, '.');
        $lastComma = strrpos($normalized, ',');
        $decimalPos = max($lastDot === false ? -1 : $lastDot, $lastComma === false ? -1 : $lastComma);

        if ($decimalPos >= 0) {
            $decimals = strlen($normalized) - $decimalPos - 1;
            if ($decimals > 0 && $decimals <= 2) {
                $integerPart = preg_replace('/[.,]/', '', substr($normalized, 0, $decimalPos)) ?? '0';
                $decimalPart = preg_replace('/\D/', '', substr($normalized, $decimalPos + 1)) ?? '0';
                $normalized = $integerPart . '.' . $decimalPart;
            } else {
                $normalized = str_replace([',', '.'], '', $normalized);
            }
        } else {
            $normalized = preg_replace('/[^0-9\-]/', '', $normalized) ?? '0';
        }

        if ($normalized === '' || $normalized === '-') {
            return 0.0;
        }

        return (float) $normalized;
    }

    private function detectCsvDelimiter(string $sampleLine): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $maxHits = -1;

        foreach ($candidates as $candidate) {
            $hits = substr_count($sampleLine, $candidate);
            if ($hits > $maxHits) {
                $best = $candidate;
                $maxHits = $hits;
            }
        }

        return $best;
    }

    private function isRowEmpty(Collection $row): bool
    {
        return $row->every(fn ($value) => $this->normalizeCell($value) === '');
    }

    private function generateUniqueProductSlug(string $name, string $sku): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = Str::slug($sku);
        }
        if ($base === '') {
            $base = 'producto';
        }

        $slug = $base;
        $i = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
            if ($i > 200) {
                $slug = "{$base}-" . Str::lower(Str::random(6));
                break;
            }
        }

        return $slug;
    }

    private function getStoreMetadataColumns(Store $store): array
    {
        $columns = [];
        $rows = Product::query()
            ->where('store_id', (int) $store->id)
            ->whereNotNull('metadata')
            ->select(['metadata'])
            ->orderByDesc('id')
            ->limit(250)
            ->get();

        foreach ($rows as $product) {
            $metadata = is_array($product->metadata) ? $product->metadata : [];
            foreach (array_keys($metadata) as $key) {
                $label = trim((string) $key);
                if ($label === '') {
                    continue;
                }

                $normalized = $this->canonicalHeaderKey($label);
                if (in_array($normalized, self::STANDARD_FIELDS, true)) {
                    continue;
                }

                if (!in_array($label, $columns, true)) {
                    $columns[] = $label;
                }

                if (count($columns) >= 12) {
                    break 2;
                }
            }
        }

        return $columns;
    }

    private function canonicalHeaderKey(string $header): string
    {
        $token = $this->normalizeHeaderToken($header);

        foreach (self::STANDARD_FIELD_ALIASES as $standard => $aliases) {
            foreach ($aliases as $alias) {
                if ($token === $this->normalizeHeaderToken((string) $alias)) {
                    return $standard;
                }
            }
        }

        return $token;
    }

    private function normalizeHeaderToken(string $header): string
    {
        $normalized = Str::ascii(Str::lower($this->normalizeCell($header)));
        $normalized = str_replace(['.', '-', '/', '\\'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', '_', trim($normalized)) ?? '';

        return trim($normalized, '_');
    }

    private function normalizeCell(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        // Remove UTF-8 BOM when present in the first cell/header.
        $text = preg_replace('/^\xEF\xBB\xBF/u', '', $text) ?? $text;

        if (!preg_match('//u', $text) && function_exists('iconv')) {
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $text);
            if ($converted !== false) {
                $text = trim($converted);
            }
        }

        if (!preg_match('//u', $text) && function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($converted !== false) {
                $text = trim($converted);
            }
        }

        if (!preg_match('//u', $text)) {
            $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
        }

        return trim($text);
    }
}
