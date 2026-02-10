<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
            [$user, $token] = $this->withDbRetry(function () use ($request) {
                $data = $request->validate([
                    'name'     => 'required|string|max:255',
                    'email'    => 'required|email:rfc,dns|unique:users,email',
                    'password' => 'required|string|min:8|confirmed',
                    'role'     => 'required|string|in:merchant,client,comerciante,cliente',
                ]);

                $role = $this->normalizeRole($data['role']);

                return DB::transaction(function () use ($data, $role) {
                    $user = User::create($this->buildUserPayload($data, $role));

                    $this->assignRole($user, $role);
                    $token = $this->createAccessToken($user);

                    return [$user, $token];
                });
            }, 'register');
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
            [$user, $token] = $this->withDbRetry(function () use ($request) {
                $credentials = $request->validate([
                    'email'    => 'required|email',
                    'password' => 'required|string',
                ]);

                $user = User::where('email', $credentials['email'])->first();

                if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                    return [null, null];
                }

                // En produccion invalidamos tokens anteriores antes de emitir uno nuevo.
                $user->tokens()->delete();
                $token = $this->createAccessToken($user);

                return [$user, $token];
            }, 'login');

            if (! $user || ! $token) {
                return response()->json([
                    'message' => 'Credenciales invalidas',
                ], 401);
            }
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
        try {
            $this->ensurePersonalAccessTokensTable();
            return $user->createToken('auth_token')->plainTextToken;
        } catch (Throwable $e) {
            throw new \RuntimeException('DB/Migrations missing: personal_access_tokens table is required.', 0, $e);
        }
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
        try {
            if (!Schema::hasTable('roles')) {
                return null;
            }

            $candidates = $role === 'merchant'
                ? ['comerciante', 'merchant', 'seller']
                : ['cliente', 'client', 'buyer'];

            $id = Role::query()->whereIn('name', $candidates)->value('id');
            return $id ? (int) $id : null;
        } catch (Throwable $e) {
            Log::warning('Legacy role resolution skipped', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function resolveRoleFromLegacyRoleId(?int $roleId): ?string
    {
        try {
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
        } catch (Throwable $e) {
            Log::warning('Legacy role reverse resolution skipped', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function ensurePersonalAccessTokensTable(): void
    {
        try {
            if (Schema::hasTable('personal_access_tokens')) {
                return;
            }

            Artisan::call('migrate', [
                '--path' => 'database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php',
                '--force' => true,
            ]);

            if (!Schema::hasTable('personal_access_tokens')) {
                throw new \RuntimeException('DB/Migrations missing: personal_access_tokens table is required.');
            }
        } catch (Throwable $e) {
            Log::error('Failed to ensure personal_access_tokens table', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            if (str_contains(strtolower($e->getMessage()), 'db/migrations missing')) {
                throw $e;
            }

            throw new \RuntimeException('DB/Migrations missing: personal_access_tokens table is required.', 0, $e);
        }
    }

    private function withDbRetry(callable $operation, string $context, int $maxAttempts = 3): mixed
    {
        $defaultConnection = config('database.default');
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $operation();
            } catch (Throwable $e) {
                if (! $this->isTransientDatabaseException($e) || $attempt >= $maxAttempts) {
                    throw $e;
                }

                Log::warning('Transient DB error during auth operation, retrying', [
                    'context' => $context,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'message' => $e->getMessage(),
                ]);

                DB::purge($defaultConnection);
                DB::reconnect($defaultConnection);

                usleep((int) ($attempt * 250 * 1000)); // 250ms, 500ms
            }
        }

        throw new \RuntimeException('Unexpected retry termination for auth DB operation.');
    }

    private function isTransientDatabaseException(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'connection refused')
            || str_contains($message, 'server has gone away')
            || str_contains($message, 'too many connections')
            || str_contains($message, 'timeout')
            || str_contains($message, 'temporarily unavailable')
            || str_contains($message, 'connection reset')
            || str_contains($message, 'sqlstate[08')
            || str_contains($message, 'sqlstate[hy000] [2002]');
    }

    private function hasMissingDatabaseEnvironment(): bool
    {
        return !(
            (bool) env('DATABASE_URL')
            || (bool) env('DB_HOST')
            || (bool) env('MYSQLHOST')
            || (bool) env('PGHOST')
        );
    }

    private function resolveAuthErrorMessage(Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if ($this->hasMissingDatabaseEnvironment()) {
            return 'Error de configuracion: faltan variables de base de datos en el backend (DATABASE_URL o MYSQLHOST/MYSQLDATABASE/MYSQLUSER/MYSQLPASSWORD).';
        }

        if (str_contains($message, 'db/migrations missing') || str_contains($message, 'personal_access_tokens')) {
            return 'DB/Migrations missing: personal_access_tokens table is required.';
        }

        if (str_contains($message, 'base table or view not found')) {
            return 'DB/Migrations missing: required database tables are not available.';
        }

        if (str_contains($message, 'unknown column')) {
            return 'DB/Migrations missing: database schema is outdated.';
        }

        if (str_contains($message, 'connection refused') || str_contains($message, 'could not find driver')) {
            if (str_contains($message, 'could not find driver')) {
                return 'Error de conexion con la base de datos: falta el driver PDO del motor configurado (mysql o pgsql).';
            }

            return 'Error de conexion con la base de datos en el servidor.';
        }

        if (str_contains($message, 'sqlstate[08') || str_contains($message, 'timeout') || str_contains($message, 'too many connections')) {
            return 'Error temporal de conexion con la base de datos. Intenta nuevamente en unos segundos.';
        }

        return 'Error interno al generar el token de acceso.';
    }

    private function resolveAuthErrorStatus(Throwable $e): int
    {
        $message = strtolower($e->getMessage());

        if ($this->hasMissingDatabaseEnvironment()) {
            return 503;
        }

        if (
            str_contains($message, 'base table or view not found') ||
            str_contains($message, 'unknown column') ||
            str_contains($message, 'connection refused') ||
            str_contains($message, 'could not find driver') ||
            str_contains($message, 'personal_access_tokens') ||
            str_contains($message, 'db/migrations missing') ||
            str_contains($message, 'sqlstate')
        ) {
            return 503;
        }

        return 500;
    }
}
