<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CategoryController extends Controller
{
    /** @var array<string>|null */
    private static ?array $categoriesColumns = null;

    // Public: List categories (dashboard merchant gets own store categories)
    public function index(Request $request)
    {
        try {
            if (!$this->hasCategoriesTable()) {
                return response()->json([]);
            }

            $user = $request->user();
            $canScopeByStore = $user && method_exists($user, 'isMerchant') && $user->isMerchant()
                && $this->categoriesColumnExists('store_id');

            if ($canScopeByStore) {
                $storeId = $user->store?->id;
                if ($storeId) {
                    return response()->json(
                        Category::query()
                            ->where('store_id', $storeId)
                            ->orderBy('name')
                            ->get()
                    );
                }
            }

            $query = Category::query();

            // Keep curated motorcycle categories when available; fallback to all if empty.
            $curatedApplied = false;
            if ($this->categoriesColumnExists('slug')) {
                $allowed = [
                    'cascos-y-proteccion',
                    'accesorios-para-moto',
                    'frenos-y-suspension',
                    'llantas-y-rines',
                    'lubricantes-y-mantenimiento',
                    'repuestos-generales',
                ];

                $curatedApplied = true;
                $query->whereIn('slug', $allowed);
            }

            if ($this->categoriesColumnExists('name')) {
                $query->orderBy('name');
            }

            $categories = $query->get();

            if ($curatedApplied && $categories->isEmpty()) {
                $fallbackQuery = Category::query();
                if ($this->categoriesColumnExists('name')) {
                    $fallbackQuery->orderBy('name');
                }
                $categories = $fallbackQuery->get();
            }

            return response()->json($categories);
        } catch (Throwable $e) {
            Log::error('Public categories listing failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([]);
        }
    }

    // Public: Show a single category
    public function show(Category $category)
    {
        return $category;
    }

    // Protected: Store a new category
    public function store(Request $request)
    {
        $user = Auth::user();
        $canCreate = method_exists($user, 'isMerchant') ? $user->isMerchant() : false;
        if (!$canCreate && !$user->hasRole(['comerciante', 'merchant', 'admin'])) {
            return response()->json(['message' => 'No tienes permisos para crear categorias.'], 403);
        }

        $store = $user->store;
        if (!$store) {
            return response()->json(['message' => 'Necesitas una tienda para crear una categoria.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Generate a globally unique slug to satisfy DB unique index.
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category = $store->categories()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    // Protected: Update a category
    public function update(Request $request, Category $category)
    {
        $user = Auth::user();
        if ($category->store->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para actualizar esta categoria.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['name'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (
                Category::query()
                    ->where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        $category->update($validated);

        return response()->json($category, 200);
    }

    // Protected: Delete a category
    public function destroy(Category $category)
    {
        $user = Auth::user();
        if ($category->store->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para eliminar esta categoria.'], 403);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la categoria porque tiene productos asociados.'], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    private function hasCategoriesTable(): bool
    {
        if (self::$categoriesColumns !== null) {
            return self::$categoriesColumns !== [];
        }

        if (!Schema::hasTable('categories')) {
            self::$categoriesColumns = [];
            return false;
        }

        self::$categoriesColumns = Schema::getColumnListing('categories');
        return true;
    }

    private function categoriesColumnExists(string $column): bool
    {
        if (!$this->hasCategoriesTable()) {
            return false;
        }

        return in_array($column, self::$categoriesColumns ?? [], true);
    }
}
