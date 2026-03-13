<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $store = Auth::user()->stores()->firstOrFail();

        $q = trim((string) $request->get('q'));
        $categories = Category::where('store_id', $store->id)
            ->when($q, fn($qb) => $qb->where('name','like',"%{$q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $store = Auth::user()->stores()->firstOrFail();
        $name  = $request->validated()['name'];

        Category::create([
            'store_id' => $store->id,
            'name'     => $name,
            'slug'     => Str::slug($name),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'CategorÃ­a creada correctamente.');
    }

    public function edit(Category $category)
    {
        $this->authorizeCategory($category);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorizeCategory($category);

        $name = $request->validated()['name'];
        $category->update([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'CategorÃ­a actualizada.');
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        // Si prefieres bloquear cuando tiene productos, cambia este bloque:
        if ($category->products()->exists()) {
            return back()->with('error', 'No puedes eliminar una categorÃ­a con productos.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'CategorÃ­a eliminada.');
    }

    // Endpoint JSON para creaciÃ³n "en vivo" desde Productos
    public function storeJson(Request $request)
    {
        $request->validate(['name' => ['required','string','max:80']]);

        $store = Auth::user()->stores()->firstOrFail();
        $name  = trim($request->input('name'));

        // Unicidad por tienda
        $exists = Category::where('store_id', $store->id)->where('name', $name)->exists();
        if ($exists) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya existe una categorÃ­a con ese nombre.',
            ], 422);
        }

        $category = Category::create([
            'store_id' => $store->id,
            'name'     => $name,
            'slug'     => Str::slug($name),
        ]);

        return response()->json([
            'ok'   => true,
            'id'   => $category->id,
            'name' => $category->name,
            'message' => 'CategorÃ­a creada.',
        ]);
    }

    private function authorizeCategory(Category $category): void
    {
        $store = Auth::user()->stores()->firstOrFail();
        abort_unless($category->store_id === $store->id, 403);
    }
}
