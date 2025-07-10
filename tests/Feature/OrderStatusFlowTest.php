<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;

class OrderStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser()
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    protected function makeStore(User $owner = null)
    {
        $owner ??= User::factory()->create();
        return Store::factory()->create([
            'user_id' => $owner->id,
        ]);
    }

    protected function makeOrder(User $user, Store $store)
    {
        return Order::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'total' => 100.00,
            'date' => now(),
            'payment_method' => 'card',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_progress_order_status()
    {
        $user = $this->makeUser();
        $store = $this->makeStore($user);
        $order = $this->makeOrder($user, $store);

        Sanctum::actingAs($user, ['*']);

        // pending -> processing
        $this->putJson("/api/orders/{$order->id}", ['status' => 'processing'])
             ->assertStatus(200)
             ->assertJsonFragment(['status' => 'processing']);

        // processing -> completed
        $this->putJson("/api/orders/{$order->id}", ['status' => 'completed'])
             ->assertStatus(200)
             ->assertJsonFragment(['status' => 'completed']);
    }

    public function test_user_can_list_and_view_their_orders()
    {
        $user = $this->makeUser();
        $store = $this->makeStore($user);
        $order = $this->makeOrder($user, $store);

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/orders')
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $order->id]);

        $this->getJson("/api/orders/{$order->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $order->id]);
    }
}
