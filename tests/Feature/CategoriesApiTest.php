<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function acting()
    {
        $u = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($u, ['*']);
        return $u;
    }

    public function test_create_category_and_slug_unique()
    {
        $this->acting();

        $payload = ['name' => 'Accesorios', 'description' => 'Desc cat'];
        $res1 = $this->postJson('/api/categories', $payload)->assertStatus(201);
        $slug = $res1->json('slug') ?? Str::slug($payload['name']);

        // Intento crear con mismo slug
        $this->postJson('/api/categories', [
            'name' => 'Accesorios', 'description' => 'Otra'
        ])->assertStatus(422); // espera validación unique:slug
    }

    public function test_update_and_show_category()
    {
        $this->acting();
        $cat = Category::factory()->create();

        $this->putJson("/api/categories/{$cat->id}", [
            'name' => 'Renamed Cat',
        ])->assertStatus(200)->assertJsonFragment(['name' => 'Renamed Cat']);

        $this->getJson("/api/categories/{$cat->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $cat->id]);
    }

    public function test_delete_category_blocked_when_has_products()
    {
        $this->acting();
        $cat = Category::factory()->create();
        Product::factory()->create(['category_id' => $cat->id]);

        $this->deleteJson("/api/categories/{$cat->id}")
             ->assertStatus(422); // o 409 Conflict si así lo manejas
    }
}
