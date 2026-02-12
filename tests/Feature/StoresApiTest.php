<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;

class StoresApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_store_and_slug_unique()
    {
        $user = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($user, ['*']);

        $payload = [
            'name' => 'Mi Tienda',
            'description' => 'Desc',
            'address' => 'Calle 123',
            'phone' => '555-555',
            'support_email' => 'tienda@example.com',
        ];

        // Use the protected route for creation
        $first = $this->postJson('/api/stores', $payload)->assertStatus(201);

        // Attempt to create a duplicate name, slug should auto-resolve uniquely
        $second = $this->postJson('/api/stores', $payload)->assertStatus(201);
        $this->assertNotEquals($first->json('slug'), $second->json('slug'));
    }

    public function test_update_show_and_block_delete_if_has_products()
    {
        $owner = User::factory()->create(['role' => 'merchant']);
        $store = Store::factory()->create(['user_id' => $owner->id]);

        // The owner is the one acting
        Sanctum::actingAs($owner, ['*']);

        // Use the protected route for update
        $this->putJson("/api/stores/{$store->id}", ['name' => 'Renamed Store'])
             ->assertStatus(200)->assertJsonFragment(['name' => 'Renamed Store']);

        // The public route for showing should still work
        $this->getJson("/api/public-stores/{$store->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $store->id]);

        // Add a product to the store
        Product::factory()->create(['store_id' => $store->id]);

        // Attempt to delete the store, should be blocked
        $this->deleteJson("/api/stores/{$store->id}")->assertStatus(422);
    }
}
