<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Store;

class StoreController extends Controller
{
    // Mostrar la tienda del usuario autenticado
    public function index()
    {
        $store = Store::where('user_id', Auth::id())->first();

        if (!$store) {
            return redirect()->route('store.create')->with('info', 'Aún no has creado tu tienda.');
        }

        return view('store.index', ['store' => $store]);
    }

    // Mostrar formulario de creación si aún no tiene tienda
    public function create()
    {
        $existingStore = Store::where('user_id', Auth::id())->first();

        if ($existingStore) {
            return redirect()->route('store.index')->with('warning', 'Ya has creado una tienda.');
        }

        return view('store.create');
    }

    // Guardar tienda
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        $store = new Store();
        $store->name = $request->name;
        $store->description = $request->description;
        $store->user_id = Auth::id();

        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('cover')) {
            $store->cover = $request->file('cover')->store('covers', 'public');
        }

        $store->save();

        return redirect()->route('products.create')->with('success', 'Tienda creada con éxito. Ahora crea tu primer producto.');
    }

    // Mostrar formulario de edición de tienda
    public function edit()
    {
        $store = Store::where('user_id', Auth::id())->firstOrFail();
        return view('store.edit', ['store' => $store]);
    }

    // Actualizar tienda
    public function update(Request $request)
    {
        $store = Store::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        $store->name = $request->name;
        $store->description = $request->description;

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $store->logo = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('cover')) {
            if ($store->cover) {
                Storage::disk('public')->delete($store->cover);
            }
            $store->cover = $request->file('cover')->store('covers', 'public');
        }

        $store->save();

        return redirect()->route('store.index')->with('success', 'Tienda actualizada correctamente.');
    }
}
