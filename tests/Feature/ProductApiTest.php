<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
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
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = \App\Models\Category::factory()->create();

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
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

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
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->deleteJson("/api/products/{$product->id}");
        $response->assertStatus(200);
    }
}
