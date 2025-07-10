<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // Obtener logo desde settings para rol 'all' o usuario null
        $logoSetting = Setting::where('key', 'logo')->where(function($query) {
            $query->where('role', 'all')->orWhereNull('user_id');
        })->first();

        $logo = $logoSetting ? $logoSetting->value : null;

        return view('auth.register', compact('logo'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => 2, // Por ejemplo, 2 para "usuario normal"
        ]);

        // No iniciar sesión automáticamente después del registro
        // Auth::login($user);

        // Redirigir a la página de login con mensaje de éxito
        return redirect('/login')->with('success', 'Registro exitoso. Por favor, inicie sesión.');
    }
}
