<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_carts()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/cart');
        $response->assertStatus(200);
    }

    public function test_can_create_cart()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user, ['*']);
        $response = $this->postJson('/api/cart', $payload);
        $response->assertStatus(201)
                 ->assertJsonFragment(['user_id' => $user->id]);
    }

    public function test_can_show_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->getJson("/api/cart/{$cart->id}");
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $cart->id]);
    }

    public function test_can_update_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $payload = [
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user, ['*']);
        $response = $this->putJson("/api/cart/{$cart->id}", $payload);
        $response->assertStatus(200)
                 ->assertJsonFragment(['user_id' => $user->id]);
    }

    public function test_can_delete_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, ['*']);
        $response = $this->deleteJson("/api/cart/{$cart->id}");
        $response->assertStatus(200);
    }
}
