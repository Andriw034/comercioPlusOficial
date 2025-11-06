<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // GET /admin/products
    public function index()
    {
        $products = Product::latest()->paginate(12);
        return view('admin.products.index', compact('products'));
    }

    // GET /admin/products/create
    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.create', compact('categories'));
    }

    // POST /admin/products
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'store_id'    => ['nullable', 'integer'],
            'user_id'     => ['nullable', 'integer'],
            'status'      => ['nullable'],
            'offer'       => ['nullable', 'numeric'],
            'average_rating' => ['nullable', 'numeric'],
        ]);

        $store = auth()->user()?->stores()->first();
        if (!$store) {
            abort(403, 'No se encontró una tienda asociada al usuario.');
        }
        $storeId = $store->id;

        $desiredSlug = $data['slug'] ?? null;
        $baseSlug = Str::slug($desiredSlug ?: $data['name']);
        if ($baseSlug === '') {
            $baseSlug = Str::random(8);
        }
        $slug = $baseSlug;
        $suffix = 1;
        while (
            Product::where('slug', $slug)
                ->where('store_id', $storeId)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }
        $data['slug'] = $slug;
        $data['store_id'] = $storeId;
        $data['user_id'] = auth()->id();

        $product = new Product($data);
        $product->status = $request->boolean('status');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public'); // "products/archivo.jpg"
            $product->image_path = $path;
        }

        $product->save();

        return redirect()->route('admin.products.index')->with('status', 'Producto creado.');
    }

    // GET /admin/products/{product}/edit
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    // PUT/PATCH /admin/products/{product}
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'store_id'    => ['nullable', 'integer'],
            'user_id'     => ['nullable', 'integer'],
            'status'      => ['nullable'],
            'offer'       => ['nullable', 'numeric'],
            'average_rating' => ['nullable', 'numeric'],
        ]);

        $storeId = $product->store_id;
        $providedSlug = $data['slug'] ?? null;
        $seedSlug = $providedSlug ?: ($request->filled('name') ? $request->input('name') : null);

        if (!empty($seedSlug)) {
            $baseSlug = Str::slug($seedSlug);
            if ($baseSlug === '') {
                $baseSlug = Str::random(8);
            }
            $slug = $baseSlug;
            $suffix = 1;
            while (
                Product::where('slug', $slug)
                    ->where('id', '!=', $product->id)
                    ->when($storeId, fn($query) => $query->where('store_id', $storeId))
                    ->exists()
            ) {
                $slug = $baseSlug.'-'.$suffix++;
            }
            $data['slug'] = $slug;
        }

        $product->fill($data);
        $product->status = $request->boolean('status');

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $path = $request->file('image')->store('products', 'public');
            $product->image_path = $path;
        }

        $product->save();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Producto actualizado.');
    }

    // DELETE /admin/products/{product}
    public function destroy(Product $product)
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Producto eliminado.');
    }
}
