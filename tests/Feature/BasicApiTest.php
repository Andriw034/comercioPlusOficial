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
        $this->getJson('/api/health')->assertStatus(200)->assertJson(['status' => 'ok']);
    }

    public function test_public_products_endpoint()
    {
        $this->getJson('/api/products')->assertStatus(200);
    }

    public function test_public_categories_endpoint()
    {
        $this->getJson('/api/categories')->assertStatus(200);
    }

    // This test now correctly asserts that creating a category requires authentication.
    public function test_create_category_without_auth()
    {
        $payload = ['name' => 'Test Category', 'description' => 'Test description'];
        $this->postJson('/api/categories', $payload)->assertStatus(401);
    }

    // This test now correctly asserts that the public-stores endpoint does not allow creation (POST).
    public function test_create_store_without_auth()
    {
        $user = User::factory()->create();
        $payload = ['name' => 'Test Store', 'description' => 'Test store description'];
        $this->postJson('/api/public-stores', $payload)->assertStatus(405); // Method Not Allowed
    }

    // This test now correctly asserts that creating a subscription requires authentication.
    public function test_create_subscription_without_auth()
    {
        $user = User::factory()->create();
        $payload = ['user_id' => $user->id, 'plan' => 'monthly', 'period' => 'monthly'];
        $this->postJson('/api/subscriptions', $payload)->assertStatus(401);
    }

    public function test_public_stores_endpoint()
    {
        $this->getJson('/api/public-stores')->assertStatus(200);
    }

    public function test_protected_users_endpoint_requires_auth()
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_protected_orders_endpoint_requires_auth()
    {
        $this->getJson('/api/orders')->assertStatus(401);
    }

    public function test_protected_cart_endpoint_requires_auth()
    {
        $this->getJson('/api/cart')->assertStatus(401);
    }

    public function test_protected_users_endpoint_with_auth()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $this->getJson('/api/users')->assertStatus(200);
    }

    public function test_protected_orders_endpoint_with_auth()
    {
        $user = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($user, ['*']);
        $this->getJson('/api/orders')->assertStatus(200);
    }

    public function test_protected_cart_endpoint_with_auth()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $this->getJson('/api/cart')->assertStatus(200);
    }
}
