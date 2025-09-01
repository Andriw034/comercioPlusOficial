<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Get the current user's subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get the active subscription for the user
        $subscription = $user->subscription()
            ->active()
            ->first();

        if (!$subscription) {
            // If no active subscription, return free tier info
            return response()->json([
                'subscription' => [
                    'type' => 'free',
                    'status' => 'active',
                    'features' => [
                        'max_products' => 10,
                        'max_stores' => 1,
                        'basic_support' => true,
                        'analytics' => false,
                        'custom_domain' => false,
                    ],
                    'limits' => [
                        'products' => 10,
                        'stores' => 1,
                    ],
                ],
                'is_active' => true,
                'is_expired' => false,
            ]);
        }

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'type' => $subscription->subscription_type,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
                'features' => $subscription->features ?? [],
                'price' => $subscription->price,
                'currency' => $subscription->currency,
            ],
            'is_active' => $subscription->isActive(),
            'is_expired' => $subscription->isExpired(),
            'days_remaining' => $subscription->ends_at
                ? now()->diffInDays($subscription->ends_at, false)
                : null,
        ]);
    }

    /**
     * Get subscription plans available.
     */
    public function plans(): JsonResponse
    {
        $plans = [
            'free' => [
                'name' => 'Gratuito',
                'price' => 0,
                'currency' => 'COP',
                'features' => [
                    'max_products' => 10,
                    'max_stores' => 1,
                    'basic_support' => true,
                    'analytics' => false,
                    'custom_domain' => false,
                    'priority_support' => false,
                    'advanced_analytics' => false,
                ],
                'limits' => [
                    'products' => 10,
                    'stores' => 1,
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 50000,
                'currency' => 'COP',
                'features' => [
                    'max_products' => 100,
                    'max_stores' => 3,
                    'basic_support' => true,
                    'analytics' => true,
                    'custom_domain' => true,
                    'priority_support' => true,
                    'advanced_analytics' => false,
                ],
                'limits' => [
                    'products' => 100,
                    'stores' => 3,
                ],
            ],
            'enterprise' => [
                'name' => 'Empresarial',
                'price' => 150000,
                'currency' => 'COP',
                'features' => [
                    'max_products' => -1, // unlimited
                    'max_stores' => -1, // unlimited
                    'basic_support' => true,
                    'analytics' => true,
                    'custom_domain' => true,
                    'priority_support' => true,
                    'advanced_analytics' => true,
                ],
                'limits' => [
                    'products' => -1,
                    'stores' => -1,
                ],
            ],
        ];

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * Upgrade or change subscription.
     */
    public function upgrade(Request $request): JsonResponse
    {
        $request->validate([
            'plan_type' => 'required|in:free,premium,enterprise',
            'payment_method' => 'required_if:plan_type,premium,enterprise',
        ]);

        $user = $request->user();
        $planType = $request->input('plan_type');

        // For free plan, just update or create free subscription
        if ($planType === 'free') {
            $subscription = $user->subscription()->first();

            if ($subscription) {
                $subscription->update([
                    'subscription_type' => 'free',
                    'status' => 'active',
                    'ends_at' => null, // Free plan doesn't expire
                    'features' => [
                        'max_products' => 10,
                        'max_stores' => 1,
                        'basic_support' => true,
                        'analytics' => false,
                        'custom_domain' => false,
                    ],
                    'price' => 0,
                ]);
            } else {
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_type' => 'free',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'features' => [
                        'max_products' => 10,
                        'max_stores' => 1,
                        'basic_support' => true,
                        'analytics' => false,
                        'custom_domain' => false,
                    ],
                    'price' => 0,
                    'currency' => 'COP',
                ]);
            }

            return response()->json([
                'message' => 'Subscription updated to free plan',
                'subscription' => $subscription,
            ]);
        }

        // For paid plans, you would integrate with payment processor here
        // This is a placeholder for the actual payment processing logic

        return response()->json([
            'message' => 'Payment processing required for ' . $planType . ' plan',
            'plan_type' => $planType,
            'payment_required' => true,
        ]);
    }
}
