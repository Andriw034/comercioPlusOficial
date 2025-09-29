<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Category;
use App\Models\Store;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Protección: requiere sesión / usuario autenticado
        $this->middleware('auth');
    }

    /**
     * Store a newly created category via AJAX/API.
     *
     * Endpoint expected: POST /api/categories
     * Body: { name, short_description?, is_popular?, popularity? }
     *
     * Returns JSON only:
     *  - 201 created on success: { message: "...", data: { ...category... } }
     *  - 422 validation errors: standard Laravel validation JSON
     *  - 403 if user has no store
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Obtener la tienda del usuario (flexible)
        $store = $this->getUserStore($user);

        if (! $store) {
            return response()->json([
                'message' => 'Necesitas crear una tienda antes de agregar categorías.'
            ], 403);
        }

        // Validación
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_popular' => ['nullable', 'boolean'],
            'popularity' => ['nullable', 'integer', 'min:0'],
        ]);

        // Normalizar valores
        $payload['is_popular'] = (bool) ($payload['is_popular'] ?? false);
        $payload['popularity'] = $payload['popularity'] ?? 0;

        // Generar slug único dentro de la tienda
        $base = Str::slug($payload['name']);
        $slug = $base;
        $i = 1;
        while (Category::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        // Crear la categoría asociada a la tienda
        $category = Category::create([
            'name' => $payload['name'],
            'slug' => $slug,
            'short_description' => $payload['short_description'] ?? null,
            'is_popular' => $payload['is_popular'],
            'popularity' => $payload['popularity'],
            'store_id' => $store->id,
        ]);

        // Respuesta JSON minimalista — ideal para AJAX: no redirecciones ni views
        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'data' => $category,
        ], 201);
    }

    /**
     * Helper: obtener la tienda del usuario (works with store/storeS)
     */
    protected function getUserStore($user): ?Store
    {
        if (! $user) return null;

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

        if (isset($user->store) && $user->store) return $user->store;
        if (isset($user->stores) && $user->stores instanceof \Illuminate\Support\Collection) {
            return $user->stores->first();
        }

        return null;
    }
}
