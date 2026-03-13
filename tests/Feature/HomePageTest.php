<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_is_reachable(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_products_api_is_reachable(): void
    {
        $this->get('/api/products')->assertStatus(200);
    }

    public function test_categories_api_is_reachable(): void
    {
        $this->get('/api/categories')->assertStatus(200);
    }
}
