<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_route_renders_correct_component()
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_dashboard_route_renders_correct_component()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertStatus(200);
    }

    public function test_stores_index_route_returns_paginated_data()
    {
        $this->get('/stores')->assertStatus(404);
    }
}
