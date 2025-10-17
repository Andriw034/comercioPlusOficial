<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de productos de la tienda actual
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store) {
            return redirect()->route('admin.dashboard')->with('info', 'Crea tu tienda para ver el catálogo.');
        }

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->latest()
            ->paginate(12);

        return view('admin.products.index', compact('products', 'store'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store) {
            if (Route::has('store.create')) {
                return redirect()->route('store.create')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
            }
            if (Route::has('store.wizard')) {
                return redirect()->route('store.wizard')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
            }
            return redirect()->route('admin.dashboard')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
        }

        // Sólo categorías de la tienda
        $categories = Category::where('store_id', $store->id)
            ->orderByDesc('is_popular')
            ->orderByDesc('popularity')
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories', 'store'));
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store) {
            if (Route::has('store.create')) {
                return redirect()->route('store.create')->with('error', 'Necesitas crear una tienda antes de agregar productos.');
            }
            return redirect()->route('admin.dashboard')->with('error', 'Necesitas crear una tienda antes de agregar productos.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('store_id', $store->id)),
            ],
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'image_url'    => 'nullable|url',
            'status'       => 'nullable|boolean',
        ]);

        // Manejo de imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $this->saveRemoteImage($request->input('image_url'), 'products');
        }

        Product::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? 0,
            'stock'       => $validated['stock'] ?? 0,
            'category_id' => $validated['category_id'],
            'user_id'     => $user->id,
            'store_id'    => $store->id,
            'image'       => $imagePath,
            'status'      => isset($validated['status']) ? (bool) $validated['status'] : true,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado correctamente.');
    }

    /**
     * Mostrar formulario edición
     */
    public function edit(Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para editar este producto.');
        }

        $categories = Category::where('store_id', $store->id)
            ->orderByDesc('is_popular')
            ->orderByDesc('popularity')
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories', 'store'));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para actualizar este producto.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('store_id', $store->id)),
            ],
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'image_url' => 'nullable|url',
            'status'    => 'nullable|boolean',
        ]);

        // Imagen nueva
        if ($request->hasFile('image')) {
            if ($product->image) {
                try { Storage::disk('public')->delete($product->image); } catch (\Throwable $e) {}
            }
            $product->image = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $newPath = $this->saveRemoteImage($request->input('image_url'), 'products');
            if ($newPath) {
                if ($product->image) {
                    try { Storage::disk('public')->delete($product->image); } catch (\Throwable $e) {}
                }
                $product->image = $newPath;
            }
        }

        $product->name        = $validated['name'];
        $product->description = $validated['description'] ?? $product->description;
        $product->price       = $validated['price'] ?? $product->price;
        $product->stock       = $validated['stock'] ?? $product->stock;
        $product->category_id = $validated['category_id'];
        $product->status      = isset($validated['status']) ? (bool) $validated['status'] : $product->status;
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Actualizar SOLO la imagen (ruta: admin.products.update-image)
     */
    public function updateImage(Request $request, Product $product)
    {
        $user  = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para actualizar la imagen de este producto.');
        }

        // Debe venir archivo o URL (uno de los dos)
        $request->validate([
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048|required_without:image_url',
            'image_url' => 'nullable|url|required_without:image',
        ]);

        $newPath = null;

        if ($request->hasFile('image')) {
            if ($product->image) {
                try { Storage::disk('public')->delete($product->image); } catch (\Throwable $e) {}
            }
            $newPath = $request->file('image')->store('products', 'public');
        } else {
            $newPath = $this->saveRemoteImage($request->input('image_url'), 'products');
            if (!$newPath) {
                return back()->with('error', 'No se pudo descargar la imagen desde la URL proporcionada.');
            }
            if ($product->image) {
                try { Storage::disk('public')->delete($product->image); } catch (\Throwable $e) {}
            }
        }

        $product->image = $newPath;
        $product->save();

        return back()->with('success', 'Imagen actualizada.');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para eliminar este producto.');
        }

        if ($product->image) {
            try { Storage::disk('public')->delete($product->image); } catch (\Throwable $e) {}
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado correctamente.');
    }

    /* -------------------------
       Helpers
    ------------------------- */

    /**
     * Descarga imagen remota y la guarda en storage público. Retorna path o null.
     */
    protected function saveRemoteImage(string $url, string $folder = 'products'): ?string
    {
        try {
            $res = Http::timeout(10)->get($url);
            if (!$res->ok()) return null;

            $contents = $res->body();
            if (!$contents) return null;

            // Detectar extensión por path (fallback jpg)
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $extension = strtolower($extension);
            if (!in_array($extension, ['jpg','jpeg','png','webp','gif'])) {
                $extension = 'jpg';
            }

            $filename = $folder . '/' . uniqid('p_') . '.' . $extension;
            Storage::disk('public')->put($filename, $contents);

            return $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Obtener la tienda del usuario de forma flexible
     */
    protected function getUserStore($user): ?Store
    {
        if (!$user) return null;

        if (method_exists($user, 'store')) {
            try { if ($user->store) return $user->store; } catch (\Throwable $e) {}
        }

        if (method_exists($user, 'stores')) {
            try { $s = $user->stores()->first(); if ($s) return $s; } catch (\Throwable $e) {}
        }

        if (isset($user->store) && $user->store) return $user->store;
        if (isset($user->stores) && $user->stores instanceof \Illuminate\Support\Collection) {
            return $user->stores->first();
        }

        return null;
    }
}
