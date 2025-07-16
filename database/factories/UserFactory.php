<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'password' => bcrypt('password'), // password por defecto
            'avatar' => null,
            'status' => 1,
            'role_id' => 1, // Ajusta según roles existentes
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
