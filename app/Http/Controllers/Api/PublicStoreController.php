<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicStoreController extends Controller
{
    public function index(Request $request)
    {
        $result = Cache::remember('public_stores_list', 300, function () {
            return Store::with('user')->get();
        });

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de tiendas públicas',
            'data' => $result,
        ]);
    }

    public function show($id)
    {
        $store = Store::findOrFail($id);
        return response()->json($store, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'sometimes|nullable|email',
            'user_id' => 'required|exists:users,id',
            'slug' => 'nullable|string|unique:stores,slug',
        ]);

        // Mapear a columnas reales
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'direccion' => $data['address'],
            'telefono' => $data['phone'],
            // email no está persistido en stores
            'user_id' => $data['user_id'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
        ];

        // Validar slug único si se generó
        if (Store::where('slug', $payload['slug'])->exists()) {
            return response()->json(['message' => 'El slug ya existe', 'errors' => ['slug' => ['The slug has already been taken.']]], 422);
        }

        $store = Store::create($payload);
        Cache::forget('public_stores_list');
        return response()->json($store, 201);
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:stores,email,' . $store->id,
            'user_id' => 'sometimes|required|exists:users,id',
            'slug' => 'nullable|string|unique:stores,slug,' . $store->id,
        ]);

        $payload = [
            'name' => $data['name'] ?? $store->name,
            'description' => $data['description'] ?? $store->description,
            'direccion' => $data['address'] ?? $store->direccion,
            'telefono' => $data['phone'] ?? $store->telefono,
            // email no está persistido en stores
            'user_id' => $data['user_id'] ?? $store->user_id,
        ];
        if (!isset($data['slug']) && isset($data['name'])) {
            $payload['slug'] = Str::slug($data['name']);
        } elseif (isset($data['slug'])) {
            $payload['slug'] = $data['slug'];
        }

        $store->update($payload);
        Cache::forget('public_stores_list');
        return response()->json($store);
    }

    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        if ($store->products()->exists()) {
            return response()->json(['message' => 'Store has products'], 422);
        }

        $store->delete();
        Cache::forget('public_stores_list');

        return response()->json(null, 204);
    }
}
