<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Str;
=======
>>>>>>> 691c95be (comentario)

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $category = Category::with('products', 'parent', 'children')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Categories retrieved successfully',
            'data' => $category,
=======
              $category = Category::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' =>  'Categories retrieved successfully',
            'data' =>  $category,
>>>>>>> 691c95be (comentario)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
<<<<<<< HEAD
        // No aplica para API
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
<<<<<<< HEAD
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        $candidate = $data['slug'] ?? Str::slug($data['name']);
        if (Category::where('slug', $candidate)->exists()) {
            return response()->json([
                'message' => 'El slug ya existe',
                'errors' => ['slug' => ['The slug has already been taken.']],
            ], 422);
        }
        $data['slug'] = $candidate;

        $category = Category::create($data);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'data' => $category,
        ], 201);
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
<<<<<<< HEAD
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json($category);
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
<<<<<<< HEAD
        // No aplica para API
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
<<<<<<< HEAD
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data' => $category,
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
<<<<<<< HEAD
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Bloquear si tiene productos
        if (method_exists($category, 'products') && $category->products()->exists()) {
            return response()->json(['message' => 'Category has products'], 422); // o 409 Conflict si así lo manejas
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ], 204);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
