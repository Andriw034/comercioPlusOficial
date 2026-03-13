<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $this->getJson('/api/users')->assertStatus(401);
        $this->getJson('/api/orders')->assertStatus(401);
        $this->getJson('/api/cart')->assertStatus(401);
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'client',
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/users')->assertStatus(200);
        $this->getJson('/api/orders')->assertStatus(200);
        $this->getJson('/api/cart')->assertStatus(200);
    }
}
