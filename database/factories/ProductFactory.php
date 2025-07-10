<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition()
    {
        $category = Category::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'image' => null,
            'category_id' => $category ? $category->id : null,
            'offer' => $this->faker->boolean(),
            'average_rating' => $this->faker->randomFloat(1, 0, 5),
            'user_id' => $user ? $user->id : null,
        ];
    }
}
