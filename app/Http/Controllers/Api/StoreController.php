<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Private stores (owner)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get();

        return response()->json($stores);
    }

    /*
    |--------------------------------------------------------------------------
    | Public stores
    |--------------------------------------------------------------------------
    */
    public function publicStores()
    {
        $stores = Store::where('is_visible', true)->get();

        return response()->json($stores);
    }

    /*
    |--------------------------------------------------------------------------
    | Create store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:stores,slug',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = isset($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $store = Store::create([
            'user_id'     => $request->user()->id,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json($store, 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Show store (public or owner via route)
    |--------------------------------------------------------------------------
    */
    public function show(Store $store)
    {
        if (!$store->is_visible && auth()->check() && $store->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($store);
    }

    /*
    |--------------------------------------------------------------------------
    | Update store
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:stores,slug,' . $store->id,
            'description' => 'nullable|string',
            'is_visible'  => 'sometimes|boolean',
        ]);

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $store->update($data);

        return response()->json($store);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete store
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($store->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la tienda porque tiene productos asociados'
            ], 422);
        }

        $store->delete();

        return response()->json(null, 204);
    }
}
