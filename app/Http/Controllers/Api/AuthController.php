<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        try {
            [$user, $token] = DB::transaction(function () use ($data, $role) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'role'     => $role,
                    'password' => Hash::make($data['password']),
                ]);

                $this->assignRole($user, $role);
                $token = $this->createAccessToken($user);

                return [$user, $token];
            });
        } catch (Throwable $e) {
            Log::error('Auth register token creation failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => $this->resolveTokenErrorMessage($e),
            ], 500);
        }

        return response()->json($this->authPayload($user, $token), 201);
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
                'email' => ['Credenciales invalidas'],
            ]);
        }

        try {
            // En produccion invalidamos tokens anteriores antes de emitir uno nuevo.
            $user->tokens()->delete();
            $token = $this->createAccessToken($user);
        } catch (Throwable $e) {
            Log::error('Auth login token creation failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => $this->resolveTokenErrorMessage($e),
            ], 500);
        }

        return response()->json($this->authPayload($user, $token));
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesion cerrada correctamente',
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
        } catch (Throwable $e) {
            // Ignorar si las tablas de roles aun no existen.
        }
    }

    private function createAccessToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    private function authPayload(User $user, string $token): array
    {
        return [
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ];
    }

    private function resolveTokenErrorMessage(Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'personal_access_tokens')) {
            return 'Error de configuracion del servidor: falta la tabla personal_access_tokens. Ejecuta migraciones en Railway con "php artisan migrate --force".';
        }

        return 'Error interno al generar el token de acceso.';
    }
}
