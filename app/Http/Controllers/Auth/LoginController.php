<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store; // âœ… para verificar si el usuario ya tiene tienda

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
     * - Si NO tiene tienda â†’ a crear tienda (/stores/create).
     * - Si SÃ tiene tienda â†’ al panel admin (/admin).
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

            // âœ… Verificamos si el usuario es comerciante y ya tiene tienda
            $isMerchant = $user->hasRole('comerciante');
            $tieneTienda = Store::where('user_id', $user->id)->exists();

            // âœ… Elegimos el destino por defecto
            if ($isMerchant && !$tieneTienda) {
                $fallback = route('store.create');  // comerciante sin tienda â†’ crear tienda
            } else {
                $fallback = route('admin.dashboard');  // tiene tienda o no es comerciante â†’ panel
            }

            // âœ… Redirige a la intended si existe, si no al fallback
            return redirect()->intended($fallback);
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
    }

    /**
     * Cierra sesiÃ³n (web).
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
            'message' => 'Credenciales invÃ¡lidas'
        ], 401);
    }
}
