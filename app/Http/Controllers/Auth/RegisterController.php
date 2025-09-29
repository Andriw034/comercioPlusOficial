<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
     * - Comerciante SIN tienda -> store.create (crear tienda, se monta el wizard Vue)
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
            'profile_photo'         => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle profile photo upload
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profiles', 'public');
        }

        // Crear usuario
        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'profile_photo_path' => $profilePhotoPath,
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

        // Si es comerciante, crear tienda automáticamente
        if ($data['role'] === 'comerciante') {
            Store::create([
                'user_id' => $user->id,
                'name' => 'Mi Tienda',
                'slug' => Str::slug('Mi Tienda-' . $user->id),
                'description' => 'Descripción de mi tienda',
            ]);
        }

        // No hacer auto-login, redirigir a login
        return redirect()
            ->route('login')
            ->with('success', 'Registro exitoso. Ahora inicia sesión.');
    }

    /**
     * Registro vía API (sin auto-login).
     */
    public function apiRegister(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'         => ['required', Rule::in(['comerciante', 'cliente'])],
            'password'     => ['required', 'string', 'min:8'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle profile photo upload
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'profile_photo_path' => $profilePhotoPath,
        ]);

        $user->assignRole($data['role']);

        if (Schema::hasColumn('users', 'role_id')) {
            $roleModel = \Spatie\Permission\Models\Role::where('name', $data['role'])->first();
            if ($roleModel) {
                $user->role_id = $roleModel->id;
                $user->save();
            }
        }

        // Si es comerciante, crear tienda automáticamente
        if ($data['role'] === 'comerciante') {
            Store::create([
                'user_id' => $user->id,
                'name' => 'Mi Tienda',
                'slug' => Str::slug('Mi Tienda-' . $user->id),
                'description' => 'Descripción de mi tienda',
            ]);
        }

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => $user,
        ], 201);
    }
}
