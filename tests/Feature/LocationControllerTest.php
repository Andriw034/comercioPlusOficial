<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_location_with_valid_data()
    {
        $data = [
            'name' => 'Ubicación de prueba',
            'address' => 'Calle Falsa 123',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ];

        $response = $this->postJson('/api/locations', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'ok',
                     'message' => 'Ubicación creada exitosamente',
                     'data' => [
                         'name' => 'Ubicación de prueba',
                         'address' => 'Calle Falsa 123',
                         'latitude' => 40.7128,
                         'longitude' => -74.0060,
                     ],
                 ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'Ubicación de prueba',
            'address' => 'Calle Falsa 123',
        ]);
    }

    /** @test */
    public function it_fails_to_create_location_with_invalid_data()
    {
        $data = [
            'name' => '', // required field empty
            'address' => str_repeat('a', 600), // too long
            'latitude' => 'not-a-number',
            'longitude' => 'not-a-number',
        ];

        $response = $this->postJson('/api/locations', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'address', 'latitude', 'longitude']);
    }
}
