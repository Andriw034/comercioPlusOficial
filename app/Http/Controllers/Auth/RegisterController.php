<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // Mostrar formulario con selección de roles
    public function showRegistrationForm()
    {
        return view('auth.register-new');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','string','lowercase','email:rfc','unique:users,email'],
            'password'  => ['required','confirmed', Password::min(6)],
            // 1 = comerciante, 2 = cliente
            'role_id'   => ['required','in:1,2'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Crear usuario
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id'  => $validated['role_id'],
            ]);

            // Asignar rol con Spatie si está instalado
            if (method_exists($user, 'assignRole')) {
                if ($validated['role_id'] == 1) {
                    $user->assignRole('comerciante');
                } else {
                    $user->assignRole('cliente');
                }
            }

            // Autologin + regenerar sesión
            Auth::login($user);
            $request->session()->regenerate();

            // Redirecciones por rol
            if ($user->role_id == 1) {
                // Comerciante → crear tienda
                return redirect()
                    ->route('store.create')
                    ->with('success', 'Registro exitoso. Por favor, crea tu tienda.');
            } else {
                // Cliente → página pública (ajusta el slug si ya tienes tienda por defecto)
                $slug = 'demo'; // cámbialo si corresponde
                if (function_exists('route') && Route::has('store.public')) {
                    return redirect()
                        ->route('store.public', ['slug' => $slug])
                        ->with('success', 'Registro exitoso. ¡Bienvenido!');
                }
                // Fallback al inicio
                return redirect('/')
                    ->with('success', 'Registro exitoso. ¡Bienvenido!');
            }
        });
    }
}
