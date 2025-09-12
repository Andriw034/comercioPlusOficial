<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        // Get current user's store
        $user = auth()->user();
        $store = $user->store;

        // Filter products by user's store if they have one
        $query = Product::with('category');

        if ($store) {
            $query->where('store_id', $store->id);
        }

        $products = $query->paginate(10);

        return view('admin.products.index', compact('products', 'store'));
    }

    public function create()
    {
        $categories = Category::all();
        $user = auth()->user();
        $store = $user->store;

        // If user doesn't have a store, redirect to create one
        if (!$store) {
            return redirect()->route('store.create')
                ->with('info', 'Necesitas crear una tienda antes de agregar productos.');
        }

        return view('products.create', compact('categories', 'store'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'image'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $user = auth()->user();
        $store = $user->store;

        // Ensure user has a store
        if (!$store) {
            return redirect()->route('store.create')
                ->with('error', 'Necesitas crear una tienda antes de agregar productos.');
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        // Associate product with user's store
        $validated['store_id'] = $store->id;
        $validated['user_id'] = $user->id;

        Product::create($validated);

        // redirige al índice del panel admin
        return redirect()->route('admin.products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'image'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        // redirige al índice del panel admin
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        // redirige al índice del panel admin
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function updateImage(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'image_url' => 'nullable|url',
            'image_file' => 'nullable|image|max:3072',
        ]);

        if (!$data['image_url'] && !$request->hasFile('image_file')) {
            return back()->withErrors(['image_url' => 'Debe proporcionar una URL o un archivo de imagen.']);
        }

        if ($request->hasFile('image_file')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image_file')->store('products', 'public');
            $product->image = $path;
            $product->image_url = null;
        } else {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image_url = $data['image_url'];
            $product->image = null;
        }

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Imagen actualizada');
    }
}
