<?php

// Test that the home page renders the 'Welcome' Inertia component.
test('muestra la Home con Inertia', function () {
    $this->get('/')
         ->assertStatus(200)
         ->assertInertia(fn ($page) => $page->component('Welcome'));
});

// Test that the public products API endpoint is working.
test('API de productos funciona', function () {
    $this->get('/api/products')->assertStatus(200);
});

// Test that the public categories API endpoint is working.
test('API de categorías funciona', function () {
    $this->get('/api/categories')->assertStatus(200);
});
