<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;

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

        if (! $store) {
            return redirect()->route('admin.dashboard')->with('info', 'Crea tu tienda para ver el catÃ¡logo.');
        }

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->latest()
            ->paginate(12);

        return view('admin.products.index', compact('products', 'store'));
    }

    /**
     * Mostrar formulario de creaciÃ³n
     */
    public function create()
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (! $store) {
            if (Route::has('store.create')) {
                return redirect()->route('store.create')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
            }
            if (Route::has('store.wizard')) {
                return redirect()->route('store.wizard')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
            }
            return redirect()->route('admin.dashboard')->with('info', 'Necesitas crear una tienda antes de agregar productos.');
        }

        // SÃ³lo categorÃ­as de la tienda
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

        if (! $store) {
            if (Route::has('store.create')) {
                return redirect()->route('store.create')->with('error', 'Necesitas crear una tienda antes de agregar productos.');
            }
            return redirect()->route('admin.dashboard')->with('error', 'Necesitas crear una tienda antes de agregar productos.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('store_id', $store->id))
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'image_url' => 'nullable|url',
            'status' => 'nullable|boolean',
        ]);

        // Manejo imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $this->saveRemoteImage($request->input('image_url'), 'products');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'stock' => $validated['stock'] ?? 0,
            'category_id' => $validated['category_id'],
            'user_id' => $user->id,
            'store_id' => $store->id,
            'image_path' => $imagePath,
            'status' => isset($validated['status']) ? (bool)$validated['status'] : true,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado correctamente.');
    }

    /**
     * Mostrar formulario ediciÃ³n
     */
    public function edit(Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (! $store || $product->store_id !== $store->id) {
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

        if (! $store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para actualizar este producto.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('store_id', $store->id))
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'image_url' => 'nullable|url',
            'status' => 'nullable|boolean',
        ]);

        // Imagen nueva: borrar anterior si existe
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                try { Storage::disk('public')->delete($product->image_path); } catch (\Throwable $e) {}
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $newPath = $this->saveRemoteImage($request->input('image_url'), 'products');
            if ($newPath) {
                if ($product->image_path) {
                    try { Storage::disk('public')->delete($product->image_path); } catch (\Throwable $e) {}
                }
                $product->image_path = $newPath;
            }
        }

        $product->name = $validated['name'];
        $product->slug = \Illuminate\Support\Str::slug($validated['name']);
        $product->description = $validated['description'] ?? $product->description;
        $product->price = $validated['price'] ?? $product->price;
        $product->stock = $validated['stock'] ?? $product->stock;
        $product->category_id = $validated['category_id'];
        $product->status = isset($validated['status']) ? (bool)$validated['status'] : $product->status;
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        if (! $store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para eliminar este producto.');
        }

        if ($product->image_path) {
            try { Storage::disk('public')->delete($product->image_path); } catch (\Throwable $e) {}
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado correctamente.');
    }
    /**
     * Activa o desactiva la promociÃ³n de un producto.
     */
    public function togglePromotion(Product $product)
    {
        $user = Auth::user();
        $store = $this->getUserStore($user);

        // Verificar que el producto pertenece a la tienda del usuario
        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'No tienes permiso para modificar este producto.');
        }

        // Cambiar el estado de la promociÃ³n
        $product->is_promo = !$product->is_promo;
        $product->save();

        return back()->with('success', 'El estado de la promociÃ³n ha sido actualizado.');
    }


    /* -------------------------
       Helpers
    ------------------------- */

    /**
     * Save remote image to public storage: returns path or null
     */
    protected function saveRemoteImage(string $url, string $folder = 'products'): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if ($contents === false) return null;

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $extension = strtolower($extension);
            if (! in_array($extension, ['jpg','jpeg','png','webp','gif'])) {
                $extension = 'jpg';
            }

            $filename = $folder . '/' . uniqid() . '.' . $extension;
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
        if (! $user) return null;

        // preferencias por mÃ©todo
        if (method_exists($user, 'store')) {
            try {
                $s = $user->store;
                if ($s) return $s;
            } catch (\Throwable $e) {}
        }

        if (method_exists($user, 'stores')) {
            try {
                $s = $user->stores()->first();
                if ($s) return $s;
            } catch (\Throwable $e) {}
        }

        // fallback a propiedades cargadas
        if (isset($user->store) && $user->store) return $user->store;
        if (isset($user->stores) && $user->stores instanceof \Illuminate\Support\Collection) {
            return $user->stores->first();
        }

        return null;
    }
}
