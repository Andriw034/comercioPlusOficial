<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_successfully()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Cliente',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'role' => 'Cliente',
        ]);
        $this->assertAuthenticated();
    }

    /**
     * Test registration with merchant role redirects to store settings.
     */
    public function test_merchant_registration_redirects_to_store_settings()
    {
        $userData = [
            'name' => 'María García',
            'email' => 'maria@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Comerciante',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect('/dashboard/settings/store');
        $this->assertDatabaseHas('users', [
            'name' => 'María García',
            'email' => 'maria@example.com',
            'role' => 'Comerciante',
        ]);
        $this->assertAuthenticated();
    }

    /**
     * Test registration with invalid email.
     */
    public function test_registration_fails_with_invalid_email()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Cliente',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test registration with password too short.
     */
    public function test_registration_fails_with_short_password()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'Cliente',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test registration with password confirmation mismatch.
     */
    public function test_registration_fails_with_password_confirmation_mismatch()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'Cliente',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test registration with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email()
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Cliente',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test registration with invalid role.
     */
    public function test_registration_fails_with_invalid_role()
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'InvalidRole',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /**
     * Test registration with missing required fields.
     */
    public function test_registration_fails_with_missing_fields()
    {
        $userData = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
        $this->assertGuest();
    }

    /**
     * Test successful user login.
     */
    public function test_user_can_login_successfully()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $loginData = [
            'email' => 'juan@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('login.post'), $loginData);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with invalid email.
     */
    public function test_login_fails_with_invalid_email()
    {
        $loginData = [
            'email' => 'invalid@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('login.post'), $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with wrong password.
     */
    public function test_login_fails_with_wrong_password()
    {
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $loginData = [
            'email' => 'juan@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->post(route('login.post'), $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with missing fields.
     */
    public function test_login_fails_with_missing_fields()
    {
        $loginData = [
            'email' => '',
            'password' => '',
        ];

        $response = $this->post(route('login.post'), $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test login with remember me functionality.
     */
    public function test_login_with_remember_me()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $loginData = [
            'email' => 'juan@example.com',
            'password' => 'password123',
            'remember' => '1',
        ];

        $response = $this->post(route('login.post'), $loginData);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        // Note: Testing remember token would require additional setup
    }

    /**
     * Test user logout.
     */
    public function test_user_can_logout()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test register form can be accessed.
     */
    public function test_register_form_can_be_accessed()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test login form can be accessed.
     */
    public function test_login_form_can_be_accessed()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test authenticated user cannot access login form.
     */
    public function test_authenticated_user_cannot_access_login_form()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('login'));

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test authenticated user cannot access register form.
     */
    public function test_authenticated_user_cannot_access_register_form()
    {
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Cliente',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('register'));

        $response->assertRedirect('/dashboard');
    }
}
