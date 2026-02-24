<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPickingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_picking_context_for_authorized_merchant(): void
    {
        $ctx = $this->makeOrderContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->getJson("/api/merchant/orders/{$ctx['order']->id}/picking")
            ->assertOk()
            ->assertJsonPath('data.order.id', $ctx['order']->id)
            ->assertJsonPath('data.lines.0.order_product_id', $ctx['line']->id)
            ->assertJsonPath('meta.totals.ordered_units', 3)
            ->assertJsonPath('meta.session.scan_consecutive_failures', 0)
            ->assertJsonPath('meta.session.fallback_required', false);
    }

    public function test_scan_applies_quantity_and_moves_order_to_picking(): void
    {
        $ctx = $this->makeOrderContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => $ctx['code'],
            'qty' => 1,
        ])->assertOk()
            ->assertJsonPath('data.line.order_product_id', $ctx['line']->id)
            ->assertJsonPath('data.line.qty_picked', 1)
            ->assertJsonPath('meta.session.scan_consecutive_failures', 0)
            ->assertJsonPath('meta.session.fallback_required', false);

        $this->assertDatabaseHas('order_products', [
            'id' => $ctx['line']->id,
            'qty_picked' => 1,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $ctx['order']->id,
            'fulfillment_status' => 'picking',
        ]);

        $this->assertDatabaseHas('order_picking_events', [
            'order_id' => $ctx['order']->id,
            'action' => 'scan_ok',
            'mode' => 'scanner',
        ]);
    }

    public function test_scan_requires_manual_fallback_after_third_failure(): void
    {
        $ctx = $this->makeOrderContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => 'BAD-CODE-1',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'CODE_NOT_FOUND')
            ->assertJsonPath('meta.session.scan_consecutive_failures', 1)
            ->assertJsonPath('meta.session.fallback_required', false);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => 'BAD-CODE-2',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'CODE_NOT_FOUND')
            ->assertJsonPath('meta.session.scan_consecutive_failures', 2)
            ->assertJsonPath('meta.session.fallback_required', false);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => 'BAD-CODE-3',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'FALLBACK_REQUIRED')
            ->assertJsonPath('meta.session.scan_consecutive_failures', 3)
            ->assertJsonPath('meta.session.fallback_required', true);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => $ctx['code'],
            'qty' => 1,
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'FALLBACK_REQUIRED')
            ->assertJsonPath('meta.session.fallback_required', true);

        $this->assertDatabaseHas('order_picking_sessions', [
            'order_id' => $ctx['order']->id,
            'user_id' => $ctx['merchant']->id,
            'scan_consecutive_failures' => 3,
            'fallback_required' => 1,
        ]);
    }

    public function test_manual_pick_resets_fallback_and_allows_complete(): void
    {
        $ctx = $this->makeOrderContext(quantity: 2);
        Sanctum::actingAs($ctx['merchant'], ['*']);

        for ($i = 1; $i <= 3; $i++) {
            $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
                'code' => "BAD-{$i}",
            ])->assertStatus(422);
        }

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/manual", [
            'action' => 'pick_item',
            'order_product_id' => $ctx['line']->id,
            'qty' => 2,
        ])->assertOk()
            ->assertJsonPath('data.line.qty_picked', 2)
            ->assertJsonPath('meta.session.scan_consecutive_failures', 0)
            ->assertJsonPath('meta.session.fallback_required', false);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/complete", [
            'completion_mode' => 'strict',
        ])->assertOk()
            ->assertJsonPath('data.fulfillment_status', 'picked');

        $this->assertDatabaseHas('orders', [
            'id' => $ctx['order']->id,
            'fulfillment_status' => 'picked',
        ]);
    }

    public function test_complete_strict_blocks_when_pending_units_exist(): void
    {
        $ctx = $this->makeOrderContext(quantity: 3);
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/manual", [
            'action' => 'pick_item',
            'order_product_id' => $ctx['line']->id,
            'qty' => 2,
        ])->assertOk();

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/complete", [
            'completion_mode' => 'strict',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'PICKING_INCOMPLETE');

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/manual", [
            'action' => 'mark_missing',
            'order_product_id' => $ctx['line']->id,
            'qty' => 1,
            'reason' => 'Sin stock en bodega',
        ])->assertOk();

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/complete", [
            'completion_mode' => 'strict',
        ])->assertOk()
            ->assertJsonPath('meta.totals.missing_units', 1)
            ->assertJsonPath('meta.totals.pending_units', 0);
    }

    public function test_picking_endpoints_forbid_orders_from_other_store(): void
    {
        $ctx = $this->makeOrderContext();
        $otherMerchant = User::factory()->create(['role' => 'merchant']);
        Store::factory()->create(['user_id' => $otherMerchant->id]);

        Sanctum::actingAs($otherMerchant, ['*']);

        $this->getJson("/api/merchant/orders/{$ctx['order']->id}/picking")
            ->assertForbidden();

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => $ctx['code'],
        ])->assertForbidden();
    }

    public function test_merchant_can_list_recent_picking_events_for_their_store(): void
    {
        $ctx = $this->makeOrderContext();
        Sanctum::actingAs($ctx['merchant'], ['*']);

        $this->postJson("/api/merchant/orders/{$ctx['order']->id}/picking/scan", [
            'code' => $ctx['code'],
            'qty' => 1,
        ])->assertOk();

        $this->getJson('/api/merchant/picking/events?limit=5')
            ->assertOk()
            ->assertJsonPath('message', 'Eventos de picking')
            ->assertJsonPath('meta.limit', 5)
            ->assertJsonPath('data.0.order_id', $ctx['order']->id)
            ->assertJsonPath('data.0.action', 'scan_ok')
            ->assertJsonPath('data.0.mode', 'scanner');
    }

    public function test_merchant_cannot_list_events_from_other_store(): void
    {
        $ctx = $this->makeOrderContext();
        $otherMerchant = User::factory()->create(['role' => 'merchant']);
        Store::factory()->create(['user_id' => $otherMerchant->id]);
        Sanctum::actingAs($otherMerchant, ['*']);

        $this->getJson('/api/merchant/picking/events?limit=5')
            ->assertOk()
            ->assertJsonPath('meta.count', 0);
    }

    private function makeOrderContext(int $quantity = 3): array
    {
        $merchant = User::factory()->create([
            'role' => 'merchant',
            'email_verified_at' => now(),
        ]);

        $customer = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        $store = Store::factory()->create(['user_id' => $merchant->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'user_id' => $merchant->id,
            'stock' => 20,
            'price' => 100000,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'store_id' => $store->id,
            'total' => 300000,
            'date' => now(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'fulfillment_status' => 'pending_pick',
        ]);

        $line = OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'qty_picked' => 0,
            'qty_packed' => 0,
            'qty_missing' => 0,
            'unit_price' => 100000,
            'base_price' => 300000,
            'tax_amount' => 0,
            'tax_rate_applied' => 0,
            'total_line' => 300000,
        ]);

        $code = '7701234567' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
        ProductCode::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => $code,
            'is_primary' => true,
        ]);

        return [
            'merchant' => $merchant,
            'store' => $store,
            'customer' => $customer,
            'product' => $product,
            'order' => $order,
            'line' => $line,
            'code' => $code,
        ];
    }
}
