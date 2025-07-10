<?php

test('muestra la Home con Inertia', function () {
    $response = $this->get('/');
    $response->assertStatus(200)
             ->assertInertia(fn ($page) => $page->component('Home'));
});

test('API de productos funciona', function () {
    $response = $this->get('/api/products');
    $response->assertStatus(200);
});

test('API de categorías funciona', function () {
    $response = $this->get('/api/categories');
    $response->assertStatus(200);
});
