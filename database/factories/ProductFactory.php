<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $baseSlug = Str::slug($name);

        return [
            'name'        => $name,
            // slug único para evitar choques en tests concurrentes
            'slug'        => $baseSlug.'-'.$this->faker->unique()->lexify('????'),
            'price'       => $this->faker->randomFloat(2, 5, 999),
            'stock'       => $this->faker->numberBetween(0, 500),
            'category_id' => Category::factory(),
            'store_id'    => Store::factory(),
            'user_id'     => \App\Models\User::factory(),
            // CLAVE: evita el error 1364 cuando el payload no envía description
            'description' => $this->faker->sentence(12),
        ];
    }
}
