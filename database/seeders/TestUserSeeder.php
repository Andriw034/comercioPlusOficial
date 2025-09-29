<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'role_id' => 2, // Merchant role
                'status' => 1,
            ]
        );

        // Create store for the user
        $store = \App\Models\Store::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Test Store',
                'slug' => 'test-store-' . uniqid(),
                'description' => 'Test store description',
                'estado' => 'activa',
                'visits' => 0,
            ]
        );

        // Create a category for the store
        $category = \App\Models\Category::create([
            'name' => 'Test Category',
            'description' => 'Test category description',
            'store_id' => $store->id,
        ]);

        // Create some test products
        \App\Models\Product::create([
            'name' => 'Test Product 1',
            'slug' => 'test-product-1',
            'description' => 'Test product description 1',
            'price' => 19.99,
            'stock' => 10,
            'image' => 'test-image.jpg',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'status' => 1,
        ]);

        \App\Models\Product::create([
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'description' => 'Test product description 2',
            'price' => 29.99,
            'stock' => 5,
            'image' => 'test-image2.jpg',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'status' => 1,
        ]);
    }
}
