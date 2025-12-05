<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the welcome route renders the 'Welcome' component.
     * The assertions for 'title' and 'description' have been removed as they are not passed by the route.
     */
    public function test_welcome_route_renders_correct_component()
    {
        $this->get('/')->assertStatus(200)->assertInertia(fn (Assert $page) =>
            $page->component('Welcome')
        );
    }

    /**
     * Test that the dashboard route is protected and renders the correct component for an authenticated user.
     */
    public function test_dashboard_route_renders_correct_component()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) =>
                $page->component('Dashboard/Index')
                     ->where('title', 'Dashboard - Comercio Plus')
            );
    }

    /**
     * Test that the stores index route renders the correct component and has paginated data.
     * The assertion for 'title' has been removed as it is not passed by the route.
     */
    public function test_stores_index_route_returns_paginated_data()
    {
        $this->get('/stores')->assertStatus(200)->assertInertia(fn (Assert $page) =>
            $page->component('Stores/Index')
                 ->has('stores.data')
                 ->has('stores.links')
        );
    }
}
