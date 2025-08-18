<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // ajusta si tu vista es otra
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Credenciales inválidas',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Mapeo simple por role_id (sin Spatie)
        // 1 => admin, 2 => comerciante, 3 => cliente
        $role = (int)($user->role_id ?? 0);

        if ($role === 2) { // comerciante
            // Si tiene tienda creada, lo enviamos al flujo de productos
            if ($user->store) {
                return redirect()->route('products.index');
            }
            // Si no tiene tienda, a crear tienda
            return redirect()->route('store.create');
        }

        if ($role === 3) { // cliente
            // Ir al catálogo público (si el user está vinculado a una tienda, úsala; si no, 'demo')
            $store = $user->store ?? null;
            $slug  = $store ? $store->slug : 'demo';
            return redirect()->route('public.store.show', ['slug' => $slug]);
        }

        if ($role === 1) { // admin
            // Si tienes panel admin
            if (route('admin.dashboard', [], false)) {
                return redirect()->route('admin.dashboard');
            }
        }

        // Fallback genérico
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
