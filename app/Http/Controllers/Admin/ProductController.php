<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Mostrar listado de productos del comerciante autenticado.
     */
    public function index(Request $request)
    {
        $store = auth()->user()->stores()->firstOrFail();

        $query = Product::with('category')
            ->where('store_id', $store->id);

        if ($search = $request->get('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category = $request->get('category_id')) {
            $query->where('category_id', $category);
        }

        $products = $query->latest('id')->paginate(12);

        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'store'));
    }

    /**
     * Mostrar formulario de creaciÃ³n de producto.
     */
    public function create()
    {
        $store = auth()->user()->stores()->firstOrFail();
        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();

        return view('admin.products.create', compact('store', 'categories'));
    }

    /**
     * Guardar nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $store = auth()->user()->stores()->firstOrFail();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $validated['store_id'] = $store->id;
        $validated['user_id']  = auth()->id();
        $validated['status']   = isset($validated['status']) ? 1 : 0;
        $validated['slug']     = $this->generateUniqueSlug($validated['name'], $store->id);

        // Guardar producto primero para tener el ID
        $product = Product::create($validated);

        // Subir imagen (si existe)
        if ($request->hasFile('image')) {
            $path = $request->file('image')
                ->store("stores/{$store->id}/products/{$product->id}", 'public');

            $product->update(['image_path' => $path]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'âœ… Producto creado correctamente.');
    }

    /**
     * Mostrar formulario de ediciÃ³n.
     */
    public function edit(Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        abort_if($product->store_id !== $store->id, 403);

        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'store'));
    }

    /**
     * Actualizar un producto existente.
     */
    public function update(Request $request, Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        abort_if($product->store_id !== $store->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($product->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $store->id, $product->id);
        }

        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Guardar nueva imagen
            $path = $request->file('image')
                ->store("stores/{$store->id}/products/{$product->id}", 'public');

            $validated['image_path'] = $path;
        }

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'âœ… Producto actualizado correctamente.');
    }

    /**
     * Eliminar un producto y su imagen asociada.
     */
    public function destroy(Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        abort_if($product->store_id !== $store->id, 403);

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'ðŸ—‘ï¸ Producto eliminado correctamente.');
    }

    /**
     * Genera un slug Ãºnico por tienda.
     */
    protected function generateUniqueSlug(string $name, int $storeId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            Product::where('store_id', $storeId)
                ->where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
