<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Listado de categorías de la tienda actual.
     */
    public function index()
    {
        $store = $this->getUserStoreOrFail();

        $categories = Category::where('store_id', $store->id)
            ->orderByDesc('is_popular')
            ->orderByDesc('popularity')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories', 'store'));
    }

    /**
     * Formulario para crear categoría.
     */
    public function create()
    {
        $store = $this->getUserStoreOrFail();
        return view('admin.categories.create', compact('store'));
    }

    /**
     * Guardar nueva categoría.
     */
    public function store(Request $request)
    {
        $store = $this->getUserStoreOrFail();

        $validated = $request->validate([
            'name'               => ['required','string','max:255',"unique:categories,name,NULL,id,store_id,{$store->id}"],
            'short_description'  => ['nullable','string','max:255'],
            'is_popular'         => ['nullable','boolean'],
            'popularity'         => ['nullable','integer','min:0'],
        ]);

        Category::create([
            'store_id'          => $store->id,
            'name'              => $validated['name'],
            'short_description' => $validated['short_description'] ?? null,
            'is_popular'        => (bool)($validated['is_popular'] ?? false),
            'popularity'        => $validated['popularity'] ?? 0,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Helper: obtener la tienda del usuario o 404.
     */
    protected function getUserStoreOrFail()
    {
        $store = auth()->user()?->store;
        abort_if(!$store, 404, 'No tienes una tienda configurada.');
        return $store;
    }
}
