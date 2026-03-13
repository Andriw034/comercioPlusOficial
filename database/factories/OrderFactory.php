<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);

        return [
            'user_id' => $user->id,
            'store_id' => $store->id,
            'total' => $this->faker->randomFloat(2, 10, 500),
            'date' => now(),
            'payment_method' => $this->faker->randomElement(['cash', 'card']),
            'status' => 'pending',
        ];
    }
}

