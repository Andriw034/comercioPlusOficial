<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
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
    private const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024;
    private const PLACEHOLDER_IMAGE_URL = '/placeholder-product.png';
    private const STANDARD_FIELDS = [
        'name',
        'sku',
        'description',
        'price',
        'stock',
        'category',
        'brand',
        'image_url',
    ];

    public function preview(UploadedFile $file): array
    {
        $this->validateFile($file);

        $rows = $this->parseFile($file);
        if ($rows->isEmpty()) {
            return [
                'headers' => [],
                'preview_rows' => [],
                'total_rows' => 0,
            ];
        }

        $headers = $rows->first();

        return [
            'headers' => $headers,
            'preview_rows' => $rows->slice(1, 10)->values()->all(),
            'total_rows' => max(0, $rows->count() - 1),
        ];
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

        $headers = $rows->first()->map(fn ($header) => trim((string) $header))->values();
        $headerKeys = $headers->map(fn ($header) => Str::lower($header))->values();

        $result = [
            'success' => true,
            'imported' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
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

            foreach ($rows->slice(1)->values() as $offset => $row) {
                $rowNumber = $offset + 2;

                try {
                    [$standard, $metadata] = $this->mapRowToData($row, $headers, $headerKeys);
                    $sku = trim((string) ($standard['sku'] ?? ''));

                    if ($sku === '') {
                        throw new \RuntimeException('SKU requerido.');
                    }

                    if (isset($standard['category']) && $standard['category'] !== '') {
                        $category = $this->findOrCreateCategory((string) $standard['category'], $store);
                        $standard['category_id'] = (int) $category->id;
                    }
                    unset($standard['category']);

                    $standard['price'] = $this->toFloat($standard['price'] ?? 0);
                    $standard['stock'] = $this->toInt($standard['stock'] ?? 0);
                    $standard['status'] = 1;

                    $incomingImage = (string) ($standard['image_url'] ?? '');
                    $standard['image_url'] = $this->resolveImageUrl($incomingImage, (int) $store->id);

                    $product = Product::query()
                        ->where('store_id', (int) $store->id)
                        ->where('sku', $sku)
                        ->first();

                    if ($product && !$upsert) {
                        throw new \RuntimeException('SKU duplicado (upsert deshabilitado).');
                    }

                    if ($product) {
                        $product->price = $standard['price'];
                        $product->stock = $standard['stock'];

                        $currentMeta = is_array($product->metadata) ? $product->metadata : [];
                        $product->metadata = array_merge($currentMeta, $metadata);
                        $product->save();
                        $result['updated']++;
                    } else {
                        $payload = array_merge($standard, [
                            'store_id' => (int) $store->id,
                            'user_id' => (int) $store->user_id,
                            'name' => (string) ($standard['name'] ?? ('Producto ' . $sku)),
                            'description' => (string) ($standard['description'] ?? ''),
                            'brand' => (string) ($standard['brand'] ?? ''),
                            'sku' => $sku,
                            'metadata' => $metadata,
                        ]);

                        Product::query()->create($payload);
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

        return $result;
    }

    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('Archivo invalido.');
        }

        if ((int) $file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new \RuntimeException('Archivo mayor a 5MB.');
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

        while (($data = fgetcsv($handle)) !== false) {
            $rows->push(collect($data)->map(fn ($value) => trim((string) $value))->values());
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
                $rowValues->push(trim((string) $cell->getFormattedValue()));
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

    private function mapRowToData(Collection $row, Collection $headers, Collection $headerKeys): array
    {
        $standard = [];
        $metadata = [];

        foreach ($headers as $index => $originalHeader) {
            $value = trim((string) ($row[$index] ?? ''));
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

    private function toFloat(mixed $value): float
    {
        $normalized = str_replace([',', ' '], ['', ''], (string) $value);
        return max(0, (float) $normalized);
    }

    private function toInt(mixed $value): int
    {
        $normalized = preg_replace('/[^0-9\-]/', '', (string) $value) ?? '0';
        return max(0, (int) $normalized);
    }
}
