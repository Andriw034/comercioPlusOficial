<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryModuleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $merchant;
    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = User::factory()->create(['role' => 'merchant']);
        $this->store = Store::factory()->create(['user_id' => $this->merchant->id]);
        $category = Category::factory()->create(['store_id' => $this->store->id]);

        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->merchant->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
            'cost_price' => 60000,
            'reorder_point' => 5,
            'allow_backorder' => false,
        ]);
    }

    public function test_tax_settings_show_and_update(): void
    {
        Sanctum::actingAs($this->merchant, ['*']);

        $this->getJson("/api/stores/{$this->store->id}/tax-settings")
            ->assertOk()
            ->assertJsonPath('data.enable_tax', false);

        $this->putJson("/api/stores/{$this->store->id}/tax-settings", [
            'enable_tax' => true,
            'tax_rate' => 0.19,
            'prices_include_tax' => false,
        ])->assertOk()
            ->assertJsonPath('data.enable_tax', true)
            ->assertJsonPath('preview.iva_calculado', 19000);
    }

    public function test_low_stock_and_adjust_stock_flow(): void
    {
        Sanctum::actingAs($this->merchant, ['*']);

        Product::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->merchant->id,
            'category_id' => $this->product->category_id,
            'stock' => 2,
            'reorder_point' => 5,
            'cost_price' => 20000,
        ]);

        $this->getJson("/api/stores/{$this->store->id}/inventory/low-stock")
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->postJson("/api/stores/{$this->store->id}/inventory/adjust", [
            'product_id' => $this->product->id,
            'new_stock' => 25,
            'note' => 'Ajuste por conteo fisico',
        ])->assertOk()
            ->assertJsonPath('data.stock_ahora', 25);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'stock_after' => 25,
        ]);
    }

    public function test_adjust_stock_requires_note(): void
    {
        Sanctum::actingAs($this->merchant, ['*']);

        $this->postJson("/api/stores/{$this->store->id}/inventory/adjust", [
            'product_id' => $this->product->id,
            'new_stock' => 22,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['note']);
    }

    public function test_report_generation_endpoint(): void
    {
        Sanctum::actingAs($this->merchant, ['*']);

        $order = Order::create([
            'user_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'total' => 200000,
            'date' => now(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'base_price' => 200000,
            'tax_amount' => 0,
            'total_line' => 200000,
        ]);

        $this->postJson("/api/stores/{$this->store->id}/reports/generate", [
            'type' => 'monthly',
        ])->assertStatus(201)
            ->assertJsonPath('data.range_type', 'monthly')
            ->assertJsonPath('data.ventas_netas', 200000);
    }

    public function test_purchase_request_create_and_transition(): void
    {
        Sanctum::actingAs($this->merchant, ['*']);

        $create = $this->postJson("/api/stores/{$this->store->id}/reorder/requests", [
            'items' => [[
                'product_id' => $this->product->id,
                'suggested_qty' => 10,
                'ordered_qty' => 12,
                'last_cost' => 60000,
            ]],
        ])->assertStatus(201);

        $requestId = $create->json('data.id');

        $this->putJson("/api/stores/{$this->store->id}/reorder/requests/{$requestId}", [
            'status' => 'received',
        ])->assertStatus(422);

        $this->putJson("/api/stores/{$this->store->id}/reorder/requests/{$requestId}", [
            'status' => 'sent',
        ])->assertOk();

        $this->putJson("/api/stores/{$this->store->id}/reorder/requests/{$requestId}", [
            'status' => 'received',
        ])->assertOk();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $requestId,
            'status' => 'received',
        ]);
    }

    public function test_module_routes_are_forbidden_for_other_merchant(): void
    {
        $otherMerchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($otherMerchant, ['*']);

        $this->getJson("/api/stores/{$this->store->id}/tax-settings")
            ->assertForbidden();
    }
}
