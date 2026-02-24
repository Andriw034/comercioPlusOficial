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

class ProductCodeLookupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_returns_product_when_code_exists_in_store(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson('/api/merchant/products/lookup-code', [
            'code' => $ctx['code'],
        ])->assertOk()
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.source', 'product_codes')
            ->assertJsonPath('data.product.id', $ctx['product']->id)
            ->assertJsonPath('data.code.value', $ctx['code'])
            ->assertJsonPath('data.code.type', 'barcode');
    }

    public function test_lookup_returns_not_found_for_unknown_code(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson('/api/merchant/products/lookup-code', [
            'code' => 'NO-EXISTE-7788',
        ])->assertStatus(404)
            ->assertJsonPath('error_code', 'PRODUCT_NOT_FOUND')
            ->assertJsonPath('suggested_action', 'CREATE_PRODUCT')
            ->assertJsonPath('data.found', false);
    }

    public function test_lookup_is_scoped_to_merchant_store(): void
    {
        $storeA = $this->makeStoreContext();
        $storeB = $this->makeStoreContext();
        Sanctum::actingAs($storeA['merchant'], ['*']);

        $this->postJson('/api/merchant/products/lookup-code', [
            'code' => $storeB['code'],
        ])->assertStatus(404)
            ->assertJsonPath('error_code', 'PRODUCT_NOT_FOUND');
    }

    public function test_lookup_requires_merchant_role(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($client, ['*']);

        $this->postJson('/api/merchant/products/lookup-code', [
            'code' => '77011223344',
        ])->assertStatus(403);
    }

    private function makeStoreContext(): array
    {
        $merchant = User::factory()->create([
            'role' => 'merchant',
            'email_verified_at' => now(),
        ]);

        $store = Store::factory()->create(['user_id' => $merchant->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $merchant->id,
            'category_id' => $category->id,
            'stock' => 7,
            'price' => 25000,
        ]);

        $code = '770LKP' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
        ProductCode::query()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => $code,
            'is_primary' => true,
        ]);

        return [
            'merchant' => $merchant,
            'store' => $store,
            'category' => $category,
            'product' => $product,
            'code' => $code,
        ];
    }
}

