<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PublicStore;

class HomeController extends Controller
{
    public function __invoke()
    {
        $categories = Category::orderBy('name')->limit(10)->get(['id','name']);

        $products = Product::where('offer', 1)
            ->orWhere('average_rating', '>=', 4.5)
            ->orderByDesc('average_rating')
            ->limit(8)
            ->get(['id','name','slug','price','image','average_rating']);

        $stores = PublicStore::where('estado', 'activa')
            ->orderByDesc('calificacion_promedio')
            ->limit(6)
            ->get(['id','nombre_tienda as name','slug','logo','calificacion_promedio','categoria_principal']);

        return view('welcome', compact('categories','products','stores'))
            ->with('title', 'ComercioPlus — Bienvenido');
    }
}
