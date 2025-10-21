<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Display the authenticated user's storefront homepage.
     */
    public function index(Request $request)
    {
        $store = auth()->user()->store ?? null;

        if (!$store || $store->estado !== 'activa') {
            return redirect()->route('dashboard')->with('error', 'Tu tienda no está activa.');
        }

        $query = Product::where('store_id', $store->id)
            ->with(['category'])
            ->published()
            ->inStock();

        // Filtros
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $products = $query->paginate(12);

        $categories = $store->categories()->whereHas('products', function($q) use ($store) {
            $q->where('store_id', $store->id)->published()->inStock();
        })->get();

        return view('storefront.index', compact('store', 'products', 'categories'));
    }

    /**
     * Display the specified product for authenticated user.
     */
    public function show(Product $product)
    {
        $store = $product->store;

        if (!$store || $store->estado !== 'activa' || !$product->isPublished() || $product->stock <= 0) {
            abort(404);
        }

        // Incrementar visitas al producto
        $product->increment('visits');

        return view('storefront.show', compact('product', 'store'));
    }

    /**
     * Display public storefront homepage by slug.
     */
    public function publicIndex(string $slug, Request $request)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        // Verificar que la tienda esté activa
        if ($store->estado !== 'activa') {
            abort(404);
        }

        $q = trim((string)$request->get('q'));
        $catId = $request->integer('category_id');

        $products = Product::query()
            ->forStore($store->id)
            ->published()
            ->inStock()
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"))
            ->when($catId, fn($qb) => $qb->where('category_id', $catId))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('store_id', $store->id)->orderBy('name')->get();

        return view('storefront.index', compact('store', 'products', 'categories', 'q', 'catId'));
    }

    /**
     * Display public product show by slug.
     */
    public function publicShow(string $slug, Product $product)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        // Seguridad: el producto debe pertenecer a la tienda y estar visible
        abort_unless($product->store_id === $store->id, 404);
        abort_if($store->estado !== 'activa' || !$product->isPublished() || $product->stock <= 0, 404);

        // Incrementar visitas
        $product->increment('visits');
        $store->increment('visits');

        return view('storefront.show', compact('store', 'product'));
    }
}
