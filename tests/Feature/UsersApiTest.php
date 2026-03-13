<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Str;

class UsersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function acting()
    {
        $u = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($u, ['*']);
        return $u;
    }

    public function test_list_users()
    {
        $this->acting();
        User::factory()->count(3)->create();
        $this->getJson('/api/users')->assertStatus(200)->assertJsonStructure([['id','name','email']]);
    }

    public function test_create_user_validates_email_uniqueness_and_format()
    {
        $this->acting();
        $email = 'taken@example.com';
        User::factory()->create(['email' => $email]);

        // email inválido
        $this->postJson('/api/users', [
            'name' => 'X',
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertStatus(422);

        // email duplicado
        $this->postJson('/api/users', [
            'name' => 'Y',
            'email' => $email,
            'password' => 'password',
        ])->assertStatus(422);

        // ok
        $this->postJson('/api/users', [
            'name' => 'Ok',
            'email' => 'ok@example.com',
            'password' => 'password',
        ])->assertStatus(201)->assertJsonFragment(['email' => 'ok@example.com']);
    }

    public function test_update_user_and_show()
    {
        $this->acting();
        $u = User::factory()->create();

        $this->putJson("/api/users/{$u->id}", [
            'name' => 'Renamed'
        ])->assertStatus(200)->assertJsonFragment(['name' => 'Renamed']);

        $this->getJson("/api/users/{$u->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $u->id, 'name' => 'Renamed']);
    }

    public function test_delete_user()
    {
        $this->acting();
        $u = User::factory()->create();

        $this->deleteJson("/api/users/{$u->id}")->assertStatus(204);
        $this->getJson("/api/users/{$u->id}")->assertStatus(404);
    }

    public function test_users_index_can_filter_by_email_like()
    {
        $this->acting();
        User::factory()->create(['email' => 'alpha@example.com']);
        User::factory()->create(['email' => 'beta@example.com']);

        $this->getJson('/api/users?search=alp')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => 'alpha@example.com'])
             ->assertJsonMissing(['email' => 'beta@example.com']);
    }
}
