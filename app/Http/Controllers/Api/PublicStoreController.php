<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublicStore;

class PublicStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicStore::query();

        // Aplicar scopes personalizados
        $query->included();
        $query->filter();
        $query->sort();

        return response()->json($query->paginate(), 200);
    }

    public function show($id)
    {
        $publicStore = PublicStore::with(request()->input('included', []))->findOrFail($id);
        return response()->json($publicStore, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'nombre_tienda' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:public_stores,slug',
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|string',
            'cover' => 'nullable|string',
            'direccion' => 'required|string',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'in:activa,inactiva',
            'horario_atencion' => 'nullable|string',
            'categoria_principal' => 'required|string|max:255',
            'calificacion_promedio' => 'nullable|numeric|min:0|max:5'
        ]);

        $publicStore = PublicStore::create($data);
        return response()->json($publicStore, 201);
    }

    public function update(Request $request, $id)
    {
        $publicStore = PublicStore::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'nombre_tienda' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:public_stores,slug,' . $id,
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|string',
            'cover' => 'nullable|string',
            'direccion' => 'sometimes|string',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'in:activa,inactiva',
            'horario_atencion' => 'nullable|string',
            'categoria_principal' => 'sometimes|string|max:255',
            'calificacion_promedio' => 'nullable|numeric|min:0|max:5'
        ]);

        $publicStore->update($data);
        return response()->json($publicStore, 200);
    }

    public function destroy($id)
    {
        $publicStore = PublicStore::findOrFail($id);
        $publicStore->delete();

        return response()->json(null, 204);
    }
}
