<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email:rfc,dns|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role'     => 'required|string|in:merchant,client,comerciante,cliente',
            ]);

            $role = $this->normalizeRole($data['role']);

            [$user, $token] = DB::transaction(function () use ($data, $role) {
                $user = User::create($this->buildUserPayload($data, $role));

                $this->assignRole($user, $role);
                $token = $this->createAccessToken($user);

                return [$user, $token];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Auth register failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => $this->resolveAuthErrorMessage($e),
            ], $this->resolveAuthErrorStatus($e));
        }

        return response()->json($this->authPayload($user, $token), 201);
    }

    public function login(Request $request)
    {
        try {
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

            // En produccion invalidamos tokens anteriores antes de emitir uno nuevo.
            $user->tokens()->delete();
            $token = $this->createAccessToken($user);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Auth login failed', [
                'user_id' => $user->id ?? null,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => $this->resolveAuthErrorMessage($e),
            ], $this->resolveAuthErrorStatus($e));
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

    private function buildUserPayload(array $data, string $role): array
    {
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = $role;
        }

        if (Schema::hasColumn('users', 'role_id')) {
            $legacyRoleId = $this->resolveLegacyRoleId($role);
            if ($legacyRoleId !== null) {
                $payload['role_id'] = $legacyRoleId;
            }
        }

        return $payload;
    }

    private function authPayload(User $user, string $token): array
    {
        $role = $user->role;
        if (empty($role)) {
            $role = $this->resolveRoleFromLegacyRoleId($user->role_id ?? null);
        }
        if (empty($role)) {
            $role = 'client';
        }

        return [
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $role,
            ],
            'token' => $token,
        ];
    }

    private function resolveLegacyRoleId(string $role): ?int
    {
        if (!Schema::hasTable('roles')) {
            return null;
        }

        $candidates = $role === 'merchant'
            ? ['comerciante', 'merchant', 'seller']
            : ['cliente', 'client', 'buyer'];

        $id = Role::query()->whereIn('name', $candidates)->value('id');
        return $id ? (int) $id : null;
    }

    private function resolveRoleFromLegacyRoleId(?int $roleId): ?string
    {
        if (!$roleId || !Schema::hasTable('roles')) {
            return null;
        }

        $name = (string) (Role::query()->where('id', $roleId)->value('name') ?? '');
        $name = strtolower($name);

        if (in_array($name, ['comerciante', 'merchant', 'seller'], true)) {
            return 'merchant';
        }

        if (in_array($name, ['cliente', 'client', 'buyer'], true)) {
            return 'client';
        }

        return null;
    }

    private function resolveAuthErrorMessage(Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'personal_access_tokens')) {
            return 'Error de configuracion del servidor: falta la tabla personal_access_tokens. Ejecuta migraciones en Railway con "php artisan migrate --force".';
        }

        if (str_contains($message, 'base table or view not found')) {
            return 'Error de configuracion del servidor: faltan tablas de base de datos. Ejecuta migraciones en Railway.';
        }

        if (str_contains($message, 'unknown column')) {
            return 'Error de configuracion del servidor: esquema de base de datos desactualizado. Ejecuta migraciones en Railway.';
        }

        if (str_contains($message, 'connection refused') || str_contains($message, 'could not find driver')) {
            return 'Error de conexion con la base de datos en el servidor.';
        }

        return 'Error interno al generar el token de acceso.';
    }

    private function resolveAuthErrorStatus(Throwable $e): int
    {
        $message = strtolower($e->getMessage());

        if (
            str_contains($message, 'base table or view not found') ||
            str_contains($message, 'unknown column') ||
            str_contains($message, 'connection refused') ||
            str_contains($message, 'could not find driver') ||
            str_contains($message, 'personal_access_tokens')
        ) {
            return 503;
        }

        return 500;
    }
}
