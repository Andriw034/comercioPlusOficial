<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index()
    {
        // Only show stores owned by the authenticated user
        $stores = Store::where('user_id', Auth::id())->get();
        return response()->json($stores, 200);
    }

    public function store(Request $request)
    {
        $request->merge([
            'slug' => $request->filled('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'))
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $store = Store::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);

        return response()->json($store, 201);
    }

    public function show($id)
    {
        // Use Route Model Binding if possible, but for now, this is fine.
        $store = Store::find($id);
        if (!$store) return response()->json(['message' => 'Tienda no encontrada'], 404);

        // A public show method should probably not check for ownership
        // but the test context implies we might be looking at a private/public distinction
        // For now, let's keep it public.
        return response()->json($store, 200);
    }

    public function update(Request $request, Store $store)
    {
        if ($store->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:stores,slug,' . $store->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $store->update($request->all());

        return response()->json($store, 200);
    }

    public function destroy(Store $store)
    {
        if ($store->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Block deletion if the store has products, as required by the test.
        if ($store->products()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la tienda porque tiene productos asociados.'], 422);
        }

        $store->delete();
        
        // Return 204 No Content for successful deletions, which is a common practice.
        return response()->json(null, 204);
    }
}
