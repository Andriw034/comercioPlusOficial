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
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
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
