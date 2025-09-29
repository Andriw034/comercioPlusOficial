<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class BasicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)
                ->assertJson(['status' => 'ok']);
    }

    public function test_public_products_endpoint()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
    }

    public function test_public_categories_endpoint()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    public function test_create_category_without_auth()
    {
        $payload = ['name' => 'Test Category', 'description' => 'Test description'];
        $response = $this->postJson('/api/categories', $payload);
        $response->assertStatus(201);
    }

    public function test_create_store_without_auth()
    {
        $user = User::factory()->create();
        $payload = [
            'name' => 'Test Store',
            'description' => 'Test store description',
            'address' => 'Test Address',
            'phone' => '123456789',
            'user_id' => $user->id
        ];
        $response = $this->postJson('/api/public-stores', $payload);
        $response->assertStatus(201);
    }

    public function test_create_subscription_without_auth()
    {
        $user = User::factory()->create();
        $payload = [
            'user_id' => $user->id,
            'plan' => 'monthly',
            'period' => 'monthly'
        ];
        $response = $this->postJson('/api/subscriptions', $payload);
        $response->assertStatus(201);
    }

    public function test_public_stores_endpoint()
    {
        $response = $this->getJson('/api/public-stores');
        $response->assertStatus(200);
    }

    public function test_protected_users_endpoint_requires_auth()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    public function test_protected_orders_endpoint_requires_auth()
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);
    }

    public function test_protected_cart_endpoint_requires_auth()
    {
        $response = $this->getJson('/api/cart');
        $response->assertStatus(401);
    }

    public function test_protected_users_endpoint_with_auth()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
    }

    public function test_protected_orders_endpoint_with_auth()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/orders');
        $response->assertStatus(200);
    }

    public function test_protected_cart_endpoint_with_auth()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/cart');
        $response->assertStatus(200);
    }
}
