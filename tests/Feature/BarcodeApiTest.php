<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BarcodeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_generate_and_get_primary_barcode_for_own_product(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->getJson("/api/products/{$ctx['product']->id}/barcode")
            ->assertOk()
            ->assertJsonPath('data.product_id', $ctx['product']->id)
            ->assertJsonPath('data.type', 'barcode')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('product_codes', [
            'store_id' => $ctx['store']->id,
            'product_id' => $ctx['product']->id,
            'type' => 'barcode',
            'is_primary' => 1,
        ]);
    }

    public function test_barcode_show_is_scoped_to_merchant_store(): void
    {
        $storeA = $this->makeStoreContext();
        $storeB = $this->makeStoreContext();
        Sanctum::actingAs($storeA['merchant'], ['*']);

        $this->getJson("/api/products/{$storeB['product']->id}/barcode")
            ->assertStatus(404);
    }

    public function test_merchant_can_search_product_by_barcode(): void
    {
        $ctx = $this->makeStoreContext();
        $code = '770BAR' . str_pad((string) $ctx['product']->id, 6, '0', STR_PAD_LEFT);

        ProductCode::query()->create([
            'product_id' => $ctx['product']->id,
            'store_id' => $ctx['store']->id,
            'type' => 'barcode',
            'value' => $code,
            'is_primary' => true,
        ]);

        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->getJson('/api/barcode/search?code=' . $code)
            ->assertOk()
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.source', 'product_codes')
            ->assertJsonPath('data.code.type', 'barcode')
            ->assertJsonPath('data.code.value', $code)
            ->assertJsonPath('data.product.id', $ctx['product']->id);
    }

    public function test_merchant_can_generate_batch_for_missing_primary_barcodes(): void
    {
        $ctx = $this->makeStoreContext();
        $merchant = $ctx['merchant'];
        $store = $ctx['store'];

        $p1 = $ctx['product'];
        $p2 = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $merchant->id,
            'category_id' => $ctx['category']->id,
        ]);
        $p3 = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $merchant->id,
            'category_id' => $ctx['category']->id,
        ]);

        ProductCode::query()->create([
            'product_id' => $p1->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => '770BATCH' . str_pad((string) $p1->id, 5, '0', STR_PAD_LEFT),
            'is_primary' => true,
        ]);

        Sanctum::actingAs($merchant, ['*']);

        $this->postJson('/api/barcode/generate-batch', ['limit' => 20])
            ->assertOk()
            ->assertJsonPath('data.generated_count', 2);

        $this->assertDatabaseCount('product_codes', 3);
        $this->assertDatabaseHas('product_codes', [
            'store_id' => $store->id,
            'product_id' => $p2->id,
            'type' => 'barcode',
            'is_primary' => 1,
        ]);
        $this->assertDatabaseHas('product_codes', [
            'store_id' => $store->id,
            'product_id' => $p3->id,
            'type' => 'barcode',
            'is_primary' => 1,
        ]);
    }

    public function test_barcode_endpoints_require_merchant_role(): void
    {
        $ctx = $this->makeStoreContext();
        $client = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($client, ['*']);

        $this->getJson('/api/barcode/search?code=77011223344')->assertStatus(403);
        $this->postJson('/api/barcode/generate-batch', [])->assertStatus(403);
        $this->getJson("/api/products/{$ctx['product']->id}/barcode")->assertStatus(403);
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
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $merchant->id,
            'category_id' => $category->id,
            'stock' => 7,
            'price' => 25000,
        ]);

        return [
            'merchant' => $merchant,
            'store' => $store,
            'category' => $category,
            'product' => $product,
        ];
    }
}
