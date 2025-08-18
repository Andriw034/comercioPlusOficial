<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Listar productos del comerciante (scoped por tienda)
     */
    public function index()
    {
        $store = Auth::user()->store;
        if (! $store) {
            return redirect()->route('store.create')
                ->with('error', 'Primero debes crear una tienda.');
        }

        $products = Product::where('store_id', $store->id)
            ->with('category')
            ->latest('id')
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    /**
     * Form de creación
     */
    public function create()
    {
        $store = Auth::user()->store;
        if (! $store) {
            return redirect()->route('store.create')
                ->with('error', 'Primero debes crear una tienda.');
        }

        // Solo categorías de la tienda del usuario
        $categories = Category::where('store_id', $store->id)
            ->orderBy('name')
            ->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Guardar nuevo producto
     */
    public function store(StoreProductRequest $request)
    {
        $store = Auth::user()->store;
        if (! $store) {
            return redirect()->route('store.create')
                ->with('error', 'Primero debes crear una tienda.');
        }

        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'store_id'    => $store->id,       // <- guardar store_id
            'user_id'     => Auth::id(),       // <- útil para auditoría
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'image'       => $imagePath,       // <- columna real
        ]);

        return redirect()->route('products.create')->with('success', '✅ Producto creado correctamente.');
    }

    /**
     * Form de edición
     */
    public function edit(Product $product)
    {
        $store = Auth::user()->store;

        // Autorizar: que el producto pertenezca a mi tienda
        if (! $store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para editar este producto.');
        }

        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Actualizar producto
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $store = Auth::user()->store;
        if (! $store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para actualizar este producto.');
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // No permitir cambios de store_id/user_id
        $product->update([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'image'       => $validated['image'] ?? $product->image,
        ]);

        return redirect()->route('products.create')->with('success', '✅ Producto actualizado correctamente.');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Product $product)
    {
        $store = Auth::user()->store;
        if (! $store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para eliminar este producto.');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.create')->with('success', '✅ Producto eliminado correctamente.');
    }
}
