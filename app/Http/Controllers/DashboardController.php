<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Si el comerciante aún NO tiene tienda, lo llevamos a crearla
        $tieneTienda = Store::where('user_id', $user->id)->exists();
        if (!$tieneTienda) {
            return redirect()
                ->route('store.create')
                ->with('info', 'Crea tu tienda para comenzar.');
        }

        // Si ya tiene tienda, mostramos el dashboard
        return view('admin.dashboard');
    }
}
