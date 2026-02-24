<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FullCommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_flow_merchant_and_client_from_store_to_picking_and_stock_discount(): void
    {
        $merchant = User::factory()->create([
            'role' => 'merchant',
            'email_verified_at' => now(),
        ]);

        $client = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($merchant, ['*']);

        $storeResponse = $this->postJson('/api/stores', [
            'name' => 'Tienda Flujo Completo',
            'description' => 'Prueba E2E',
            'is_visible' => true,
        ])->assertStatus(201);

        $storeId = (int) $storeResponse->json('id');

        $this->putJson("/api/stores/{$storeId}", [
            'description' => 'Descripcion actualizada',
            'phone' => '3001234567',
            'address' => 'Calle 1 # 2-3',
        ])->assertOk();

        $categoryResponse = $this->postJson('/api/categories', [
            'name' => 'Repuestos',
            'description' => 'Categoria para flujo completo',
        ])->assertStatus(201);

        $categoryId = (int) $categoryResponse->json('id');

        $manualProductResponse = $this->postJson('/api/products', [
            'name' => 'Filtro Manual',
            'price' => 50000,
            'stock' => 12,
            'category_id' => $categoryId,
            'description' => 'Producto creado manualmente',
        ])->assertStatus(201);

        $manualProductId = (int) $manualProductResponse->json('data.id');

        $scannerCreatedResponse = $this->postJson('/api/merchant/inventory/create-from-scan', [
            'code' => '770123450001',
            'code_type' => 'barcode',
            'name' => 'Cadena Scanner',
            'category_id' => $categoryId,
            'price' => 90000,
            'initial_qty' => 8,
            'request_id' => 'flow-create-scan-1',
        ])->assertOk();

        $scannerProductId = (int) $scannerCreatedResponse->json('data.product.id');
        $this->assertSame(8, (int) $scannerCreatedResponse->json('data.product.stock'));

        $this->postJson('/api/merchant/inventory/scan-in', [
            'code' => '770123450001',
            'qty' => 2,
            'reason' => 'purchase',
            'reference' => 'FAC-PROV-1001',
            'request_id' => 'flow-scan-in-1',
        ])->assertOk()
            ->assertJsonPath('data.product.stock', 10);

        $manualBeforeOrder = (int) Product::query()->findOrFail($manualProductId)->stock;
        $scannerBeforeOrder = (int) Product::query()->findOrFail($scannerProductId)->stock;

        Sanctum::actingAs($client, ['*']);

        $cartResponse = $this->postJson('/api/cart', [])->assertStatus(201);
        $cartId = (int) $cartResponse->json('id');

        $this->postJson('/api/cart-products', [
            'cart_id' => $cartId,
            'product_id' => $manualProductId,
            'quantity' => 1,
            'unit_price' => 50000,
        ])->assertStatus(201);

        $this->postJson('/api/cart-products', [
            'cart_id' => $cartId,
            'product_id' => $scannerProductId,
            'quantity' => 2,
            'unit_price' => 90000,
        ])->assertStatus(201);

        $orderResponse = $this->postJson('/api/orders', [
            'store_id' => $storeId,
            'items' => [
                ['product_id' => $manualProductId, 'quantity' => 1],
                ['product_id' => $scannerProductId, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'status' => 'pending',
        ])->assertStatus(201);

        $orderId = (int) $orderResponse->json('data.id');

        // Pendiente: no debe descontar stock aun.
        $this->assertSame($manualBeforeOrder, (int) Product::query()->findOrFail($manualProductId)->stock);
        $this->assertSame($scannerBeforeOrder, (int) Product::query()->findOrFail($scannerProductId)->stock);

        Sanctum::actingAs($merchant, ['*']);

        $this->getJson('/api/merchant/orders')
            ->assertOk()
            ->assertJsonFragment(['id' => $orderId]);

        $pickingContext = $this->getJson("/api/merchant/orders/{$orderId}/picking")
            ->assertOk();

        $lines = collect($pickingContext->json('data.lines'));
        $manualLineId = (int) ($lines->firstWhere('product_id', $manualProductId)['order_product_id'] ?? 0);
        $scannerLineId = (int) ($lines->firstWhere('product_id', $scannerProductId)['order_product_id'] ?? 0);

        $this->assertTrue($manualLineId > 0);
        $this->assertTrue($scannerLineId > 0);

        $this->postJson("/api/merchant/orders/{$orderId}/picking/scan", [
            'code' => '770123450001',
            'qty' => 2,
        ])->assertOk()
            ->assertJsonPath('data.line.order_product_id', $scannerLineId)
            ->assertJsonPath('data.line.qty_picked', 2);

        $this->postJson("/api/merchant/orders/{$orderId}/picking/manual", [
            'action' => 'pick_item',
            'order_product_id' => $manualLineId,
            'qty' => 1,
        ])->assertOk()
            ->assertJsonPath('data.line.order_product_id', $manualLineId)
            ->assertJsonPath('data.line.qty_picked', 1);

        $this->postJson("/api/merchant/orders/{$orderId}/picking/complete", [
            'completion_mode' => 'strict',
        ])->assertOk()
            ->assertJsonPath('data.fulfillment_status', 'picked');

        // Completar picking no descuenta (regla actual del proyecto).
        $this->assertSame($manualBeforeOrder, (int) Product::query()->findOrFail($manualProductId)->stock);
        $this->assertSame($scannerBeforeOrder, (int) Product::query()->findOrFail($scannerProductId)->stock);

        // El descuento de stock actual ocurre cuando pasa a estado de pago.
        $this->putJson("/api/merchant/orders/{$orderId}/status", ['status' => 'paid'])
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertSame($manualBeforeOrder - 1, (int) Product::query()->findOrFail($manualProductId)->stock);
        $this->assertSame($scannerBeforeOrder - 2, (int) Product::query()->findOrFail($scannerProductId)->stock);

        // No debe descontar de nuevo al pasar a completed.
        $this->putJson("/api/merchant/orders/{$orderId}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertSame($manualBeforeOrder - 1, (int) Product::query()->findOrFail($manualProductId)->stock);
        $this->assertSame($scannerBeforeOrder - 2, (int) Product::query()->findOrFail($scannerProductId)->stock);
    }
}

