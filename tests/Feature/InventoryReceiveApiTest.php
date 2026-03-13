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

class InventoryReceiveApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_in_with_existing_product_increments_stock_and_creates_movement(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson('/api/merchant/inventory/scan-in', [
            'code' => $ctx['code'],
            'qty' => 5,
            'reason' => 'purchase',
            'reference' => 'FAC-PROV-8891',
            'request_id' => 'scan-in-req-1',
        ])->assertOk()
            ->assertJsonPath('data.product.id', $ctx['product']->id)
            ->assertJsonPath('data.product.stock', 15)
            ->assertJsonPath('data.movement.type', 'purchase')
            ->assertJsonPath('data.movement.reason', 'purchase')
            ->assertJsonPath('data.movement.reference', 'FAC-PROV-8891');

        $this->assertDatabaseHas('products', [
            'id' => $ctx['product']->id,
            'stock' => 15,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'store_id' => $ctx['store']->id,
            'product_id' => $ctx['product']->id,
            'type' => 'purchase',
            'reason' => 'purchase',
            'request_id' => 'scan-in-req-1',
            'quantity' => 5,
            'stock_after' => 15,
        ]);
    }

    public function test_scan_in_with_same_request_id_is_idempotent(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $payload = [
            'code' => $ctx['code'],
            'qty' => 3,
            'reason' => 'purchase',
            'request_id' => 'scan-in-idempotent-1',
        ];

        $this->postJson('/api/merchant/inventory/scan-in', $payload)
            ->assertOk()
            ->assertJsonPath('data.idempotent', false);

        $this->postJson('/api/merchant/inventory/scan-in', $payload)
            ->assertOk()
            ->assertJsonPath('data.idempotent', true);

        $this->assertDatabaseHas('products', [
            'id' => $ctx['product']->id,
            'stock' => 13,
        ]);

        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_movements', [
            'request_id' => 'scan-in-idempotent-1',
            'quantity' => 3,
            'stock_after' => 13,
        ]);
    }

    public function test_scan_in_returns_not_found_when_code_does_not_exist(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson('/api/merchant/inventory/scan-in', [
            'code' => 'NO-EXISTE-123',
            'qty' => 1,
        ])->assertStatus(404)
            ->assertJsonPath('error_code', 'PRODUCT_NOT_FOUND')
            ->assertJsonPath('suggested_action', 'CREATE_PRODUCT');
    }

    public function test_create_from_scan_creates_product_code_and_inbound_movement(): void
    {
        $ctx = $this->makeStoreContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson('/api/merchant/inventory/create-from-scan', [
            'code' => '770000009999',
            'code_type' => 'barcode',
            'name' => 'Cadena 428H x120',
            'category_id' => $ctx['category']->id,
            'price' => 45000,
            'initial_qty' => 5,
            'reason' => 'purchase',
            'reference' => 'FAC-NEW-001',
            'request_id' => 'create-scan-req-1',
        ])->assertOk()
            ->assertJsonPath('data.product.name', 'Cadena 428H x120')
            ->assertJsonPath('data.product.stock', 5)
            ->assertJsonPath('data.movement.type', 'purchase')
            ->assertJsonPath('data.movement.request_id', 'create-scan-req-1');

        $created = Product::query()
            ->where('store_id', $ctx['store']->id)
            ->where('name', 'Cadena 428H x120')
            ->first();

        $this->assertNotNull($created);

        $this->assertDatabaseHas('product_codes', [
            'store_id' => $ctx['store']->id,
            'product_id' => $created->id,
            'type' => 'barcode',
            'value' => '770000009999',
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'store_id' => $ctx['store']->id,
            'product_id' => $created->id,
            'type' => 'purchase',
            'request_id' => 'create-scan-req-1',
            'quantity' => 5,
            'stock_after' => 5,
        ]);
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
            'stock' => 10,
            'price' => 100000,
        ]);

        $code = '7701234567' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT);
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

