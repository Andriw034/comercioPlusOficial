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

    protected function acting()
    {
        $u = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($u, ['*']);
        return $u;
    }

    public function test_create_store_and_slug_unique()
    {
        $this->acting();

        $payload = [
            'name' => 'Mi Tienda',
            'description' => 'Desc',
            'address' => 'Calle 123',
            'phone' => '555-555',
            'email' => 'tienda@example.com',
            'user_id' => User::factory()->create()->id,
        ];

        $this->postJson('/api/public-stores', $payload)->assertStatus(201);

        // Duplicado por slug mismo nombre
        $this->postJson('/api/public-stores', $payload)->assertStatus(422);
    }

    public function test_update_show_and_block_delete_if_has_products()
    {
        $this->acting();
        $owner = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $owner->id]);

        $this->putJson("/api/public-stores/{$store->id}", ['name' => 'Renamed Store'])
             ->assertStatus(200)->assertJsonFragment(['name' => 'Renamed Store']);

        $this->getJson("/api/public-stores/{$store->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $store->id]);

        Product::factory()->create(['store_id' => $store->id]);

        $this->deleteJson("/api/public-stores/{$store->id}")->assertStatus(422); // o 409 si lo usas
    }
}
