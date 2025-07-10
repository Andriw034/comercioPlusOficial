<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_product_to_cart_via_api()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'price' => 19.99,
            'stock' => 50,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
        ];

        $this->postJson('/api/cart-products', $payload)
             ->assertStatus(201)
             ->assertJsonFragment(['cart_id' => $cart->id, 'product_id' => $product->id]);
    }
}
