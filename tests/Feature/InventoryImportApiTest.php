<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryImportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_prioritizes_barcode_match_over_sku(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $existing = Product::factory()->create([
            'store_id' => $ctx['store']->id,
            'user_id' => $ctx['merchant']->id,
            'category_id' => $ctx['category']->id,
            'name' => 'Bujia NGK',
            'sku' => 'SKU-OLD-1',
            'stock' => 2,
            'price' => 10000,
        ]);

        ProductCode::query()->create([
            'store_id' => $ctx['store']->id,
            'product_id' => $existing->id,
            'type' => 'barcode',
            'value' => '770000100001',
            'is_primary' => true,
        ]);

        $csv = $this->csvContent(
            ['BARCODE', 'CODIGO', 'PRODUCTO', 'CANTIDAD', 'P/PUBLICO', 'COD GRUPO'],
            [
                ['770000100001', 'SKU-NEW-1', 'Bujia NGK Iridium', '9', '15000', 'General'],
            ],
        );

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $this->postJson('/api/inventory/import', [
            'file' => $file,
            'upsert' => true,
        ])->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('updated', 1);

        $this->assertDatabaseHas('products', [
            'id' => $existing->id,
            'name' => 'Bujia NGK Iridium',
            'sku' => 'SKU-NEW-1',
            'stock' => 9,
            'price' => 15000,
        ]);

        $this->assertDatabaseHas('product_codes', [
            'store_id' => $ctx['store']->id,
            'product_id' => $existing->id,
            'type' => 'barcode',
            'value' => '770000100001',
            'is_primary' => true,
        ]);
    }

    public function test_import_generates_internal_barcode_when_missing_and_keeps_sku_nullable(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $csv = $this->csvContent(
            ['PRODUCTO', 'CANTIDAD', 'P/PUBLICO', 'COD GRUPO'],
            [
                ['Cadena 428H', '5', '45000', 'General'],
                ['Kit arrastre 520', '3', '98000', 'General'],
            ],
        );

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $this->postJson('/api/inventory/import', [
            'file' => $file,
            'upsert' => true,
        ])->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonPath('updated', 0);

        $this->assertDatabaseHas('products', [
            'store_id' => $ctx['store']->id,
            'name' => 'Cadena 428H',
            'sku' => null,
        ]);

        $this->assertDatabaseHas('products', [
            'store_id' => $ctx['store']->id,
            'name' => 'Kit arrastre 520',
            'sku' => null,
        ]);

        $this->assertDatabaseHas('product_codes', [
            'store_id' => $ctx['store']->id,
            'type' => 'barcode',
            'value' => 'CP-000001',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('product_codes', [
            'store_id' => $ctx['store']->id,
            'type' => 'barcode',
            'value' => 'CP-000002',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('store_counters', [
            'store_id' => $ctx['store']->id,
            'next_product_barcode' => 3,
        ]);
    }

    public function test_preview_includes_import_resolution_fields(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $existing = Product::factory()->create([
            'store_id' => $ctx['store']->id,
            'user_id' => $ctx['merchant']->id,
            'category_id' => $ctx['category']->id,
            'name' => 'Filtro aceite',
            'sku' => 'SKU-FILTRO',
        ]);

        ProductCode::query()->create([
            'store_id' => $ctx['store']->id,
            'product_id' => $existing->id,
            'type' => 'barcode',
            'value' => '7701111222233',
            'is_primary' => true,
        ]);

        $csv = $this->csvContent(
            ['BARCODE', 'PRODUCTO', 'CANTIDAD', 'P/PUBLICO'],
            [
                ['7701111222233', 'Filtro aceite premium', '4', '26000'],
                ['', 'Producto nuevo sin codigo', '1', '9000'],
            ],
        );

        $file = UploadedFile::fake()->createWithContent('preview.csv', $csv);

        $this->postJson('/api/inventory/preview', [
            'file' => $file,
        ])->assertOk()
            ->assertJsonPath('analysis_rows.0.matched_by', 'barcode')
            ->assertJsonPath('analysis_rows.0.action', 'update')
            ->assertJsonPath('analysis_rows.0.resolved_barcode', '7701111222233')
            ->assertJsonPath('analysis_rows.0.barcode_source', 'excel')
            ->assertJsonPath('analysis_rows.1.matched_by', 'none')
            ->assertJsonPath('analysis_rows.1.action', 'create');
    }

    private function makeStoreContext(): array
    {
        $merchant = User::factory()->create([
            'role' => 'merchant',
            'email_verified_at' => now(),
        ]);

        $store = Store::factory()->create([
            'user_id' => $merchant->id,
        ]);

        $category = Category::factory()->create([
            'store_id' => $store->id,
            'name' => 'General',
        ]);

        return [
            'merchant' => $merchant,
            'store' => $store,
            'category' => $category,
        ];
    }

    private function csvContent(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);
        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);
        return $content;
    }
}
