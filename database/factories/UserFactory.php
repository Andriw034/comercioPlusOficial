<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
<<<<<<< HEAD
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => null,
            'status' => true,
            'address' => $this->faker->address(),
            'role_id' => Role::factory(),
=======
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'password' => bcrypt('password'), // password por defecto
            'avatar' => null,
            'status' => 1,
            'role_id' => 1, // Ajusta según roles existentes
            'created_at' => now(),
            'updated_at' => now(),
>>>>>>> 691c95be (comentario)
        ];
    }
}
