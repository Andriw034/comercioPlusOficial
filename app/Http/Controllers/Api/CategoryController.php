<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Public: List all categories
    public function index()
    {
        return Category::all();
    }

    // Public: Show a single category
    public function show(Category $category)
    {
        return $category;
    }

    // Protected: Store a new category
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole(['comerciante', 'admin'])) {
            return response()->json(['message' => 'No tienes permisos para crear categorías.'], 403);
        }

        $store = $user->store;
        if (!$store) {
            return response()->json(['message' => 'Necesitas una tienda para crear una categoría.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Generate a unique slug for the category within the store
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while ($store->categories()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category = $store->categories()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    // Protected: Update a category
    public function update(Request $request, Category $category)
    {
        $user = Auth::user();
        if ($category->store->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para actualizar esta categoría.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json($category, 200);
    }

    // Protected: Delete a category
    public function destroy(Category $category)
    {
        $user = Auth::user();
        if ($category->store->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para eliminar esta categoría.'], 403);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la categoría porque tiene productos asociados.'], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
