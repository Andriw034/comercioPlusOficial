<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * CategoryController
 *
 * CRUD de categorías por tienda (cada comerciante administra sus propias categorías).
 *
 * - Rutas definidas bajo /admin (nombres: admin.categories.*)
 * - Asegura que un usuario sólo gestione categorías de su(s) tienda(s).
 */
class CategoryController extends Controller
{
    public function __construct()
    {
        // Aseguramos que el usuario esté autenticado
        $this->middleware('auth');
    }

    /**
     * Mostrar listado de categorías de la tienda actual.
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
     * Mostrar formulario para crear categoría.
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_popular' => ['nullable', 'boolean'],
            'popularity' => ['nullable', 'integer', 'min:0'],
        ]);

        // Normalizar valores
        $data['is_popular'] = (bool) ($data['is_popular'] ?? false);
        $data['popularity'] = $data['popularity'] ?? 0;

        // Generar slug único por tienda
        $data['slug'] = $this->makeUniqueSlug($data['name'], $store->id);

        // Asignar store_id
        $data['store_id'] = $store->id;

        $category = Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Mostrar formulario para editar categoría.
     */
    public function edit(Category $category)
    {
        $store = $this->getUserStoreOrFail();

        $this->abortIfNotOwner($category, $store);

        return view('admin.categories.edit', compact('category', 'store'));
    }

    /**
     * Actualizar categoría.
     */
    public function update(Request $request, Category $category)
    {
        $store = $this->getUserStoreOrFail();

        $this->abortIfNotOwner($category, $store);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_popular' => ['nullable', 'boolean'],
            'popularity' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_popular'] = (bool) ($data['is_popular'] ?? false);
        $data['popularity'] = $data['popularity'] ?? 0;

        // Si renombra la categoría, regenerar slug (manteniendo unicidad por tienda)
        if ($category->name !== $data['name']) {
            $data['slug'] = $this->makeUniqueSlug($data['name'], $store->id, $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminar categoría (soft delete no implementado aquí; se borra físicamente).
     */
    public function destroy(Category $category)
    {
        $store = $this->getUserStoreOrFail();

        $this->abortIfNotOwner($category, $store);

        // Si quieres evitar eliminar categorías que todavía tengan productos,
        // descomenta el siguiente bloque y ajusta la relación products().
        // if ($category->products()->exists()) {
        //     return back()->with('error', 'No se puede eliminar: la categoría contiene productos.');
        // }

        $category->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }

    /* -------------------------
     | Helpers privados
     | ------------------------- */

    /**
     * Obtiene la tienda del usuario actual (primer store).
     * Si no existe, aborta con mensaje sugerente.
     */
    protected function getUserStoreOrFail()
    {
        $user = Auth::user();

        // Ajusta según tu relación: aquí asumo $user->stores() existe y devuelve una colección
        $store = $user->stores()->first();

        if (!$store) {
            // Redirigimos a la ruta donde se crea tienda (ajusta el nombre si tu ruta difiere)
            abort(403, 'No tienes una tienda activa. Crea tu tienda antes de gestionar categorías.');
        }

        return $store;
    }

    /**
     * Verifica que la categoría pertenezca a la tienda del usuario; si no, aborta 403.
     */
    protected function abortIfNotOwner(Category $category, $store)
    {
        if ($category->store_id !== $store->id) {
            abort(403, 'No tienes permisos para gestionar esta categoría.');
        }
    }

    /**
     * Genera un slug único por tienda.
     *
     * @param  string  $name
     * @param  int     $storeId
     * @param  int|null $ignoreId  ID a ignorar (útil en update)
     * @return string
     */
    protected function makeUniqueSlug(string $name, int $storeId, int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while ($this->slugExistsInStore($slug, $storeId, $ignoreId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * Comprueba si el slug ya existe en la misma store.
     */
    protected function slugExistsInStore(string $slug, int $storeId, int $ignoreId = null): bool
    {
        $query = Category::where('store_id', $storeId)->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        return $query->exists();
    }
}
