<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserSubscription;
use Carbon\Carbon;

class UserSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some example subscriptions for testing
        UserSubscription::create([
            'user_id' => 1,
            'subscription_type' => 'free',
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonth(),
            'ends_at' => null,
            'features' => json_encode([
                'max_products' => 10,
                'max_stores' => 1,
                'basic_support' => true,
                'analytics' => false,
                'custom_domain' => false,
            ]),
            'price' => 0,
            'currency' => 'COP',
            'payment_method' => null,
            'transaction_id' => null,
        ]);

        UserSubscription::create([
            'user_id' => 2,
            'subscription_type' => 'premium',
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonth(),
            'ends_at' => Carbon::now()->addMonth(),
            'features' => json_encode([
                'max_products' => 100,
                'max_stores' => 3,
                'basic_support' => true,
                'analytics' => true,
                'custom_domain' => true,
                'priority_support' => true,
                'advanced_analytics' => false,
            ]),
            'price' => 50000,
            'currency' => 'COP',
            'payment_method' => 'credit_card',
            'transaction_id' => 'TX1234567890',
        ]);
    }
}
