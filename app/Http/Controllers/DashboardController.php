<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Si el comerciante aÃºn NO tiene tienda, lo llevamos a crearla
        $tieneTienda = Store::where('user_id', $user->id)->exists();
        if (!$tieneTienda) {
            return redirect()
                ->route('store.create')
                ->with('info', 'Crea tu tienda para comenzar.');
        }

        // EstadÃ­sticas del dashboard
        $store = $user->stores->first();
        $totalProducts = Product::where('store_id', $store->id)->count();
        $activeProducts = Product::where('store_id', $store->id)->where('status', 1)->count();
        $categories = Category::where('store_id', $store->id)->count();
        $recentProducts = Product::where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'activeProducts',
            'categories',
            'recentProducts'
        ));
    }
}
