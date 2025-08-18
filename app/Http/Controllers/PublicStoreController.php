<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class PublicStoreController extends Controller
{
    /**
     * Mostrar la tienda pública por slug
     */
    public function show($slug)
    {
        $store = Store::with('user.products') // Carga tienda + usuario + productos
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public-store.show', compact('store'));
    }
}