
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can register', function () {
    $password = 'Password123!';

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ];

    // Simulate a POST request to the registration endpoint
    $response = $this->post('/register', $userData);

    // Assert the user was redirected to the dashboard
    $response->assertRedirect('/dashboard');

    // Assert that the user was actually created in the database
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // Assert that we can authenticate as the new user
    $this->assertAuthenticated();
});
