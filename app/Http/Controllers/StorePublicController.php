<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorePublicController extends Controller
{
    /**
     * Muestra la tienda pública según el slug.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $store = Store::where('slug', $slug)
            ->with('user.products') // cargamos los productos del usuario
            ->firstOrFail();

        return view('store.public', compact('store'));
    }

    /**
     * Muestra el formulario para crear una nueva tienda.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('store.create');
    }

    /**
     * Almacena una nueva tienda en la base de datos.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stores,slug',
            'primary_color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'logo' => 'nullable|image',
            'background' => 'nullable|image',
        ]);

        $store = new Store();
        $store->name = $request->input('name');
        $store->slug = Str::slug($request->input('slug'));
        $store->primary_color = $request->input('primary_color');
        $store->description = $request->input('description');
        $store->user_id = auth()->id();

        if ($request->hasFile('logo')) {
            try {
                $store->logo = $request->file('logo')->store('stores', 'public');
            } catch (\Exception $e) {
                return back()->withErrors(['logo' => 'Error al subir el logo: ' . $e->getMessage()]);
            }
        }

        if ($request->hasFile('background')) {
            try {
                $store->background = $request->file('background')->store('stores', 'public');
            } catch (\Exception $e) {
                return back()->withErrors(['background' => 'Error al subir el fondo: ' . $e->getMessage()]);
            }
        }

        $store->save();

        return redirect()->route('dashboard')->with('success', '¡Tienda creada correctamente!');
    }
}
