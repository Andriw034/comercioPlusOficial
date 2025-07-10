<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Mostrar formulario de registro con el logo (si existe).
     */
    public function showRegistrationForm()
    {
        // Obtener logo desde settings para rol 'all' o usuario null
        $logoSetting = Setting::where('key', 'logo')
            ->where(function ($query) {
                $query->where('role', 'all')->orWhereNull('user_id');
            })
            ->first();

        $logo = $logoSetting ? $logoSetting->value : null;

        return view('auth.register', compact('logo'));
    }

    /**
     * Registrar usuario y redirigir según rol/tienda:
     * - Comerciante SIN tienda -> store.create (crear tienda)
     * - Comerciante CON tienda -> admin.dashboard
     * - Cliente -> admin.dashboard
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'                  => ['required', Rule::in(['comerciante', 'cliente'])],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        // Crear usuario (si tu User tiene cast 'hashed', el Hash::make sería opcional)
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Asignar rol con Spatie
        $user->assignRole($data['role']);

        // Si existe la columna role_id, sincronizarla con el id del rol
        if (Schema::hasColumn('users', 'role_id')) {
            $roleModel = \Spatie\Permission\Models\Role::where('name', $data['role'])->first();
            if ($roleModel) {
                $user->role_id = $roleModel->id;
                $user->save();
            }
        }

        // Auto-login
        Auth::login($user);

        // Redirecciones según rol y existencia de tienda
        if ($user->hasRole('comerciante')) {
            $tieneTienda = Store::where('user_id', $user->id)->exists();

            if ($tieneTienda) {
                // Comerciante con tienda -> dashboard
                return redirect()
                    ->route('admin.dashboard')
                    ->with('success', '¡Bienvenido! Accede a tu panel.');
            }

            // Comerciante sin tienda -> IR A CREAR TIENDA (antes fallaba por store.index)
            return redirect()
                ->route('store.create')
                ->with('info', 'Crea tu tienda para comenzar.');
        }

        // Cliente -> dashboard
        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Registro exitoso. Bienvenido.');
    }

    /**
     * Registro vía API (sin auto-login).
     */
    public function apiRegister(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(['comerciante', 'cliente'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        if (Schema::hasColumn('users', 'role_id')) {
            $roleModel = \Spatie\Permission\Models\Role::where('name', $data['role'])->first();
            if ($roleModel) {
                $user->role_id = $roleModel->id;
                $user->save();
            }
        }

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => $user,
        ], 201);
    }
}
