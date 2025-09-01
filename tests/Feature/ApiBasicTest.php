<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ApiBasicTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario para autenticación
        $this->user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        // Obtener token de autenticación
        $response = $this->postJson('/api/v1/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->token = $response->json('token') ?? $response->json('access_token');
    }

    /** @test */
    public function can_get_users_list()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'meta']);
    }

    /** @test */
    public function can_create_user()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/v1/users', [
                             'name' => 'New User',
                             'email' => 'newuser@example.com',
                             'password' => 'password',
                             'role_id' => 2,
                         ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['email' => 'newuser@example.com']);
    }

    /** @test */
    public function can_update_user()
    {
        $user = $this->user;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson("/api/v1/users/{$user->id}", [
                             'name' => 'Updated Name',
                         ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    /** @test */
    public function can_delete_user()
    {
        $user = $this->user;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);
    }

    // Similar tests can be created for stores, products, orders, etc.
}
