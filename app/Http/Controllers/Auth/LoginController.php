<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store; // ✅ para verificar si el usuario ya tiene tienda

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login (web).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario (web) y redirige:
     * - Si NO tiene tienda → a crear tienda (/stores/create).
     * - Si SÍ tiene tienda → al panel admin (/admin).
     *
     * Si existe una URL "intended" previa de Laravel, se respeta,
     * pero usando el destino calculado como fallback.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ✅ Verificamos si el usuario es comerciante y ya tiene tienda
            $isMerchant = $user->hasRole('comerciante');
            $tieneTienda = Store::where('user_id', $user->id)->exists();

            // ✅ Elegimos el destino por defecto
            if ($isMerchant && !$tieneTienda) {
                $fallback = route('store.create');  // comerciante sin tienda → crear tienda
            } else {
                $fallback = route('admin.dashboard');  // tiene tienda o no es comerciante → panel
            }

            // ✅ Redirige a la intended si existe, si no al fallback
            return redirect()->intended($fallback);
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
    }

    /**
     * Cierra sesión (web).
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Login para API con Sanctum.
     * Retorna token + user en JSON.
     */
    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Create token using Sanctum
            $tokenResult = $user->createToken('API Token', ['*']);
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'user'  => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Credenciales inválidas'
        ], 401);
    }
}
