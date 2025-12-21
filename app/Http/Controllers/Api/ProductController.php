<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Listado público de productos
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'store']);

        // 🔍 Búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 📂 Filtro por categoría
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // ⭐ Ordenamiento seguro
        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                default      => $query->latest(),
            };
        } else {
            $query->latest();
        }

        return response()->json(
            $query->paginate(12)
        );
    }

    /**
     * Crear producto (AUTENTICADO)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        // 🔐 Obtener tienda del usuario autenticado
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        $data['store_id'] = $store->id;
        $data['user_id']  = $request->user()->id;

        // Evitar error NOT NULL
        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        // Generar slug único si no viene
        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 1;

            while (Product::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $data['slug'] = $slug;
        }

        $product = Product::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $product->load('category', 'store'),
        ], 201);
    }

    /**
     * Mostrar producto público
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => 'ok',
            'data'   => $product->load('category', 'store'),
        ]);
    }

    /**
     * Actualizar producto (SOLO PROPIETARIO)
     */
    public function update(Request $request, Product $product)
    {
        // 🔐 Seguridad: solo dueño
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|nullable|string|max:255|unique:products,slug,' . $product->id,
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        // Regenerar slug si viene vacío
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? $product->name);
            $slug = $base;
            $i = 1;

            while (
                Product::where('slug', $slug)
                    ->where('id', '!=', $product->id)
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }

            $data['slug'] = $slug;
        }

        $product->update($data);

        return response()->json([
            'status' => 'updated',
            'data'   => $product->load('category', 'store'),
        ]);
    }

    /**
     * Eliminar producto (SOLO PROPIETARIO)
     */
    public function destroy(Request $request, Product $product)
    {
        // 🔐 Seguridad: solo dueño
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $product->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}
