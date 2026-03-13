<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Listado pÃºblico de productos (solo de tiendas visibles y productos activos)
     */
    public function index()
    {
        $products = Product::with(['category', 'store'])
            // En admin el status es tinyint 0/1; aquÃ­ usamos 1 = activo
            ->where('status', 1)
            ->whereHas('store', fn ($q) => $q->where('is_visible', true))
            ->latest()
            ->paginate(24)
            ->through(function ($p) {
                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'price'      => $p->price,
                    'stock'      => $p->stock,
                    'status'     => (int) $p->status,
                    'category'   => $p->category?->name,
                    'store'      => $p->store?->name,
                    // usar el campo correcto y convertirlo en URL pÃºblica
                    'image_url'  => $p->image_path ? Storage::url($p->image_path) : null,
                    'slug'       => $p->slug,
                    'created_at' => optional($p->created_at)->toISOString(),
                ];
            });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'title'    => 'Productos - Comercio Plus',
        ]);
    }

    /**
     * PÃ¡gina de creaciÃ³n pÃºblica (si la usas con Inertia).
     * Si no la usas, puedes ignorarla.
     */
    public function create()
    {
        return Inertia::render('Products/Create');
    }

    /**
     * Mostrar detalle pÃºblico
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'store'])->findOrFail($id);

        // normalizamos la URL de imagen para la vista
        $product->image_url = $product->image_path ? Storage::url($product->image_path) : null;

        return Inertia::render('Products/Show', [
            'product' => [
                'id'         => $product->id,
                'name'       => $product->name,
                'description'=> $product->description,
                'price'      => $product->price,
                'stock'      => $product->stock,
                'status'     => (int) $product->status,
                'category'   => $product->category?->name,
                'store'      => $product->store?->name,
                'image_url'  => $product->image_url,
                'slug'       => $product->slug,
                'created_at' => optional($product->created_at)->toISOString(),
            ],
        ]);
    }

    // Si en pÃºblico no creas/actualizas, puedes dejar store/update/destroy vacÃ­os o eliminarlos.
    public function store(Request $request) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
