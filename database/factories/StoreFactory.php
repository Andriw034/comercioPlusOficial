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
            'logo' => $this->faker->imageUrl(),
            'cover' => $this->faker->imageUrl(),
            'primary_color' => $this->faker->hexColor(),
            'description' => $this->faker->sentence(10),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'estado' => $this->faker->randomElement(['activa', 'inactiva']),
            'horario_atencion' => $this->faker->sentence(5),
            'categoria_principal' => $this->faker->word(),
            'calificacion_promedio' => $this->faker->randomFloat(1, 0, 5),
            'user_id' => User::factory(),
        ];
    }
}
