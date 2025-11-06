<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function authenticated(Request $request, $user)
    {
        // Ajusta estos nombres de rol a los que uses en tu app
        $isMerchant = $user->hasRole('admin_comerciante') || $user->hasRole('merchant') || $user->role === 'merchant';
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';

        if ($isMerchant || $isAdmin) {
            // ¿Tiene tienda?
            $store = \App\Models\Store::query()->where('user_id', $user->id)->first();

            if (!$store) {
                // No tiene tienda -> llevar al wizard o create
                return redirect()->route(Route::has('store.create') ? 'store.create' : 'store.wizard');
            }

            // Sí tiene tienda -> panel
            return redirect()->route('admin.dashboard');
        }

        // Cliente comprador -> a storefront (o a welcome)
        return redirect()->route(Route::has('storefront.index') ? 'storefront.index' : 'welcome');
    }
}
