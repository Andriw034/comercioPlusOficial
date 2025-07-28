<?php

namespace App\Http\Controllers;

use App\Models\PublicStore;
use App\Models\Store;
use Illuminate\Http\Request;

class PublicStoreController extends Controller
{
    // Mostrar todas las tiendas públicas
    public function index()
    {
        $publicStores = PublicStore::with('store')->get();
        return view('public_stores.index', compact('publicStores'));
    }

    // Mostrar formulario para crear publicación
    public function create()
    {
        $stores = Store::all(); // Mostrar lista de tiendas
        return view('public_stores.create', compact('stores'));
    }

    // Guardar una nueva publicación
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'nombre_publico' => 'required|string|max:255',
            'slug' => 'required|string|unique:public_stores,slug',
        ]);

        PublicStore::create($request->all());

        return redirect()->route('publicstores.index')->with('success', 'Tienda publicada con éxito.');
    }

    // Mostrar una tienda pública
    public function show($id)
    {
        $publicStore = PublicStore::with('store')->findOrFail($id);
        return view('publicstores.show', compact('publicStore'));
    }

    // Formulario para editar
    public function edit($id)
    {
        $publicStore = PublicStore::findOrFail($id);
        $stores = Store::all();
        return view('public_stores.edit', compact('publicStore', 'stores'));
    }

    // Actualizar publicación
    public function update(Request $request, $id)
    {
        $publicStore = PublicStore::findOrFail($id);

        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'nombre_publico' => 'required|string|max:255',
            'slug' => 'required|string|unique:public_stores,slug,' . $id,
        ]);

        $publicStore->update($request->all());

        return redirect()->route('public_stores.index')->with('success', 'Tienda actualizada.');
    }

    // Eliminar publicación
    public function destroy($id)
    {
        $publicStore = PublicStore::findOrFail($id);
        $publicStore->delete();

        return redirect()->route('public_stores.index')->with('success', 'Tienda eliminada.');
    }
}
