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

        return redirect()->route('post.login');
    }

    public function postLoginRedirect()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'comerciante') {
            $store = $user->store;
            if (! $store) {
                return redirect()->route('store.create');
            }
            return redirect()->route('products.index');
        }

        if ($user->role === 'cliente') {
            return redirect()->route('welcome');
        }

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
