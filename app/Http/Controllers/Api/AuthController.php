<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|string|in:merchant,client,comerciante,cliente',
        ]);

        $role = $this->normalizeRole($data['role']);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $role,
            'password' => Hash::make($data['password']),
        ]);

        $this->assignRole($user, $role);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas'],
            ]);
        }

        // Producción: invalidar tokens anteriores
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    private function normalizeRole(string $role): string
    {
        $role = strtolower($role);
        if ($role === 'comerciante') {
            return 'merchant';
        }
        if ($role === 'cliente') {
            return 'client';
        }
        return $role === 'merchant' ? 'merchant' : 'client';
    }

    private function assignRole(User $user, string $role): void
    {
        $spatieRole = $role === 'merchant' ? 'comerciante' : 'cliente';
        try {
            if (!Role::where('name', $spatieRole)->exists()) {
                Role::create(['name' => $spatieRole, 'guard_name' => 'web']);
            }
            $user->assignRole($spatieRole);
        } catch (\Throwable $e) {
            // Ignore if Spatie tables are not ready
        }
    }
}
