<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // Mostrar formulario con selección de roles
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email:rfc','unique:users,email'],
            'password' => ['required','confirmed', Password::min(6)],
            'role'     => ['required','in:comerciante,cliente'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Crear usuario
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => $validated['role'],
            ]);

            // Asignar rol con Spatie si está instalado
            if (method_exists($user, 'assignRole')) {
                $user->assignRole($validated['role']);
            }

            // Autologin + regenerar sesión
            Auth::login($user);
            $request->session()->regenerate();

            // Redirecciones por rol
            if ($user->role === 'comerciante') {
                return redirect()
                    ->route('store.create')
                    ->with('success', 'Registro exitoso. Por favor, crea tu tienda.');
            }

            return redirect()
                ->route('welcome')
                ->with('success', 'Registro exitoso. ¡Bienvenido!');
        });
    }
}
