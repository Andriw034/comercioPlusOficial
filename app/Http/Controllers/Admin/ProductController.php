<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $store = auth()->user()->stores()->firstOrFail();

        $query = Product::with('category')
            ->where('store_id', $store->id);

        // Search functionality
        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                         ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['0','1'], true)) {
            $query->where('status', (int) $request->status);
        }

        $products = $query->latest('id')->paginate(12);

        // Categories for filter dropdown
        $categories = Category::where('store_id', $store->id)
            ->orderBy('name')
            ->get();

        return view('admin.products.index', compact('products', 'categories', 'store'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $store = auth()->user()->stores()->firstOrFail();
        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'store'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();

        $data = $request->validated();

        // Pertenencia
        $data['store_id'] = $store->id;
        $data['user_id']  = auth()->id();

        // Status como tinyint(1)
        $data['status'] = isset($data['status']) ? (int) (bool) $data['status'] : 1;

        // Slug único por tienda
        $data['slug'] = $this->generateUniqueSlug($data['name'], $store->id);

        // Imagen
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('products/' . $store->id, 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        if ((int)$product->store_id !== (int)$store->id) {
            abort(403);
        }
        return view('admin.products.show', compact('product', 'store'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        if ((int)$product->store_id !== (int)$store->id) {
            abort(403);
        }
        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'store'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        if ((int)$product->store_id !== (int)$store->id) {
            abort(403);
        }

        $data = $request->validated();

        // Status como tinyint(1)
        if (array_key_exists('status', $data)) {
            $data['status'] = (int) (bool) $data['status'];
        }

        // Regenerar slug solo si cambió el nombre; asegurar unicidad ignorando el propio ID
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $store->id, $product->id);
        }

        // Imagen
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store('products/' . $store->id, 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $store = auth()->user()->stores()->firstOrFail();
        if ((int)$product->store_id !== (int)$store->id) {
            abort(403);
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Genera un slug único por tienda.
     *
     * @param  string      $name
     * @param  int         $storeId
     * @param  int|null    $ignoreProductId  ID a ignorar (en update)
     * @return string
     */
    protected function generateUniqueSlug(string $name, int $storeId, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        $exists = function (string $try) use ($storeId, $ignoreProductId): bool {
            $q = Product::where('store_id', $storeId)->where('slug', $try);
            if ($ignoreProductId) {
                $q->where('id', '!=', $ignoreProductId);
            }
            return $q->exists();
        };

        while ($exists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
