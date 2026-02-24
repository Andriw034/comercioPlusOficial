<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
    }

    public function test_can_create_product()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);

        $payload = [
            'name' => 'Test Product',
            'price' => 100,
            'stock' => 10,
            'description' => 'Test description',
            'store_id' => $store->id,
            'category_id' => $category->id,
        ];

        Sanctum::actingAs($user, ['*']);
        $response = $this->postJson('/api/products', $payload);
        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Product']);
    }

    public function test_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_can_update_product()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $payload = [
            'name' => 'Updated Product',
            'price' => 150,
        ];

        Sanctum::actingAs($user, ['*']);
        $response = $this->putJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Product']);
    }

    public function test_can_delete_product()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->deleteJson("/api/products/{$product->id}");
        $response->assertStatus(200);
    }

    public function test_can_create_product_with_primary_code()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->postJson('/api/products', [
            'name' => 'Filtro aceite NKD',
            'price' => 35000,
            'stock' => 6,
            'description' => 'Filtro premium',
            'category_id' => $category->id,
            'codes' => [
                ['type' => 'barcode', 'value' => '770123450001', 'is_primary' => true],
            ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Filtro aceite NKD')
            ->assertJsonPath('data.product_codes.0.type', 'barcode')
            ->assertJsonPath('data.product_codes.0.value', '770123450001');

        $productId = (int) data_get($response->json(), 'data.id');
        $this->assertDatabaseHas('product_codes', [
            'store_id' => $store->id,
            'product_id' => $productId,
            'type' => 'barcode',
            'value' => '770123450001',
            'is_primary' => 1,
        ]);
    }

    public function test_can_update_product_and_replace_codes()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        ProductCode::query()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => '770OLD001',
            'is_primary' => true,
        ]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Producto actualizado',
            'codes' => [
                ['type' => 'barcode', 'value' => '770NEW002', 'is_primary' => true],
                ['type' => 'sku', 'value' => 'SKU-ABC-22'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Producto actualizado')
            ->assertJsonFragment(['value' => '770NEW002'])
            ->assertJsonFragment(['value' => 'SKU-ABC-22']);

        $this->assertDatabaseMissing('product_codes', [
            'product_id' => $product->id,
            'store_id' => $store->id,
            'value' => '770OLD001',
        ]);

        $this->assertDatabaseHas('product_codes', [
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => '770NEW002',
        ]);

        $this->assertDatabaseHas('product_codes', [
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => 'sku',
            'value' => 'SKU-ABC-22',
        ]);
    }

    public function test_rejects_duplicate_code_within_same_store()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['store_id' => $store->id]);

        $existingProduct = Product::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        ProductCode::query()->create([
            'product_id' => $existingProduct->id,
            'store_id' => $store->id,
            'type' => 'barcode',
            'value' => '770DUP999',
            'is_primary' => true,
        ]);

        Sanctum::actingAs($user, ['*']);
        $this->postJson('/api/products', [
            'name' => 'Nuevo producto',
            'price' => 1000,
            'stock' => 1,
            'description' => 'x',
            'category_id' => $category->id,
            'codes' => [
                ['type' => 'barcode', 'value' => '770DUP999', 'is_primary' => true],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['codes']);
    }
}
