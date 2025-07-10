<?php

namespace Tests\Feature;

use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class WebRoutesTest extends TestCase
{
    public function test_welcome_route_renders_correct_component()
    {
        $response = $this->get('/', ['X-Inertia' => 'true']);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Welcome')
                 ->where('title', 'Bienvenido a Comercio Plus')
                 ->where('description', 'La plataforma de e-commerce para tiendas de repuestos de motos')
        );
    }

    public function test_dashboard_route_renders_correct_component()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard', ['X-Inertia' => 'true']);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Dashboard/Index')
                 ->where('title', 'Dashboard - Comercio Plus')
        );
    }

    public function test_stores_index_route_returns_paginated_data()
    {
        $response = $this->get('/stores', ['X-Inertia' => 'true']);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Stores/Index')
                 ->has('stores.data')
                 ->has('stores.links')
                 ->where('title', 'Tiendas - Comercio Plus')
        );
    }
}
