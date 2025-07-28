<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    // Muestra el formulario de registro
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Procesa el registro de usuario
    public function register(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role_id'  => 'required|in:1,2', // 1 para comerciante, 2 para cliente
        ]);

        // Crear el usuario
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $request->role_id,
        ]);

        // Asignar el rol con Spatie (Si usas roles con Spatie)
        if ($request->role_id == 1) {
            // Rol de comerciante
            $user->assignRole('comerciante');
        } elseif ($request->role_id == 2) {
            // Rol de cliente
            $user->assignRole('cliente');
        }

        // Redirigir según el rol seleccionado
        if ($user->role_id == 1) {
            // Si es comerciante, redirigir a la creación de tienda
            return redirect()->route('store.create')->with('success', 'Registro exitoso. Por favor, crea tu tienda.');
        } else {
            // Si es cliente, redirigir a la página principal u otra lógica de cliente
            return redirect()->route('welcome')->with('success', 'Registro exitoso. Bienvenido.');
        }
    }
}
