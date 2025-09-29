<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(10),
            'logo_path' => null,
            'logo_url' => $this->faker->imageUrl(),
            'background_path' => null,
            'background_url' => $this->faker->imageUrl(),
            'theme_primary' => $this->faker->hexColor(),
            'user_id' => User::factory(),
        ];
    }
}
