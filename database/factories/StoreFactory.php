<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'description' => $this->faker->sentence,
            'user_id' => User::factory(),
            'is_visible' => true,
        ];
    }
}
