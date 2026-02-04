<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Listado público de productos
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $perPage = ($perPage > 0 && $perPage <= 50) ? $perPage : 12;

        $query = Product::query()
            ->included() // permite ?included=store,category
            ->with(['category', 'store']);

        // 🔍 Búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 📂 Filtro por categoría (acepta category o category_id)
        if ($request->filled('category') || $request->filled('category_id')) {
            $query->where('category_id', $request->get('category', $request->get('category_id')));
        }

        // 🏪 Filtro por tienda
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // 🎯 Estado / visibilidad
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ⭐ Ordenamiento seguro
        $sort = $request->get('sort', 'recent');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->latest(),
        };

        $paginated = $query->paginate($perPage);
        $paginated->getCollection()->transform(function ($item) {
            return $this->withImageUrl($item);
        });

        return response()->json($paginated);
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
            'status'      => 'nullable|in:draft,active',
            'image'       => 'nullable|image|max:2048',
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

        // Cargar imagen si se envía
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/' . $store->id, 'public');
            $data['image_path'] = $path;
        }

        $product = Product::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
        ], 201);
    }

    /**
     * Mostrar producto público
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => 'ok',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
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
            'status'      => 'sometimes|in:draft,active',
            'image'       => 'sometimes|nullable|image|max:2048',
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

        // Reemplazar imagen si viene
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products/' . $product->store_id, 'public');
        }

        $product->update($data);

        return response()->json([
            'status' => 'updated',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
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

        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Producto eliminado correctamente',
        ]);
    }

    private function withImageUrl(Product $product): Product
    {
        if ($product->image_path) {
            $product->image_url = Storage::disk('public')->url($product->image_path);
        }
        return $product;
    }
}
