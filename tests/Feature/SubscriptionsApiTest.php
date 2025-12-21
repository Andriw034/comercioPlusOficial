<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class SubscriptionsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function acting()
    {
        $u = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($u, ['*']);
        return $u;
    }

    public function test_subscribe_and_cancel()
    {
        $user = $this->acting();

        // Alta
        $this->postJson('/api/subscriptions', [
            'user_id' => $user->id,
            'plan' => 'basic',
            'period' => 'monthly', // ajusta a tu validación real
        ])->assertStatus(201)->assertJsonFragment(['plan' => 'basic']);

        // Renovación/expiración (mock simple): listar debe incluirla
        $this->getJson('/api/subscriptions')
             ->assertStatus(200)
             ->assertJsonFragment(['user_id' => $user->id]);

        // Baja
        $id = $this->getJson('/api/subscriptions')->json()[0]['id'];
        $this->deleteJson("/api/subscriptions/{$id}")->assertStatus(204);
    }

    public function test_subscription_limits_validation()
    {
        $user = $this->acting();

        // Falta plan
        $this->postJson('/api/subscriptions', [
            'user_id' => $user->id,
        ])->assertStatus(422);

        // Period no permitido
        $this->postJson('/api/subscriptions', [
            'user_id' => $user->id,
            'plan' => 'basic',
            'period' => 'decennial',
        ])->assertStatus(422);
    }
}
