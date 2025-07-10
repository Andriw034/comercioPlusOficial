<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'           => ['nullable','string','max:100'],
            'category_id' => ['nullable','integer'],
            'offer'       => ['nullable','in:0,1'],
            'min_price'   => ['nullable','numeric','min:0'],
            'max_price'   => ['nullable','numeric','min:0'],
            'sort'        => ['nullable','in:price_asc,price_desc,rating_desc,newest'],
            'page'        => ['nullable','integer','min:1'],
            'per_page'    => ['nullable','integer','min:1','max:50'],
        ]);

        $perPage = $data['per_page'] ?? 12;

        $q = Product::query()
            ->select([
                'id','name','slug','description','image_path','image','price','stock',
                'category_id','offer','average_rating','store_id','created_at'
            ])
            ->with('category:id,name');

        if (!empty($data['q'])) {
            $term = $data['q'];
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                   ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($data['category_id'])) {
            $q->where('category_id', (int)$data['category_id']);
        }

        if (isset($data['offer'])) {
            $q->where('offer', (int)$data['offer'] === 1);
        }

        if (!empty($data['min_price'])) {
            $q->where('price', '>=', (float)$data['min_price']);
        }

        if (!empty($data['max_price'])) {
            $q->where('price', '<=', (float)$data['max_price']);
        }

        switch ($data['sort'] ?? null) {
            case 'price_asc':  $q->orderBy('price', 'asc'); break;
            case 'price_desc': $q->orderBy('price', 'desc'); break;
            case 'rating_desc':$q->orderBy('average_rating', 'desc'); break;
            case 'newest':     $q->orderBy('created_at', 'desc'); break;
            default:           $q->orderBy('name'); break;
        }

        $page = $q->paginate($perPage)->withQueryString();

        $page->getCollection()->transform(function ($p) {
            $img = $p->image_path ?: $p->image;
            if ($img && !str_starts_with($img, 'http')) {
                $img = asset(ltrim($img, '/'));
            }

            return [
                'id'       => $p->id,
                'name'     => $p->name,
                'slug'     => $p->slug,
                'price'    => (float)$p->price,
                'stock'    => (int)$p->stock,
                'rating'   => $p->average_rating ? (float)$p->average_rating : null,
                'image'    => $img,
                'offer'    => (bool)$p->offer,
                'category' => optional($p->category)->name,
                'created'  => $p->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No aplica para API
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'store_id'    => 'required|exists:stores,id',
            'description' => 'nullable|string',
        ]);

        // Set user_id from authenticated user
        $data['user_id'] = $request->user()->id;

        // Si la BD tiene NOT NULL en description, evitamos 1364
        if (!array_key_exists('description', $data) || $data['description'] === null) {
            $data['description'] = '';
        }

        // Generar slug si no viene
        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $candidate = $base;
            $i = 1;
            while (Product::where('slug', $candidate)->exists()) {
                $candidate = $base.'-'.$i;
                $i++;
            }
            $data['slug'] = $candidate;
        }

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => 'ok',
            'data' => $product->load('store', 'category'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|nullable|string|max:255|unique:products,slug,'.$product->id,
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'store_id'    => 'sometimes|required|exists:stores,id',
            'description' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? $product->name);
            $candidate = $base;
            $i = 1;
            while (Product::where('slug', $candidate)->where('id', '!=', $product->id)->exists()) {
                $candidate = $base.'-'.$i;
                $i++;
            }
            $data['slug'] = $candidate;
        }

        $product->update($data);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}
