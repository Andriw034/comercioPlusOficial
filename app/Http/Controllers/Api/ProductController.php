<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Support\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private readonly MediaUploader $mediaUploader)
    {
    }

    /**
     * Listado p??blico de productos
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $perPage = ($perPage > 0 && $perPage <= 50) ? $perPage : 12;

        try {
            if (!Schema::hasTable('products')) {
                return response()->json($this->emptyPagination($perPage));
            }

            $query = Product::query()
                ->included()
                ->with(['category', 'store']);

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('category') || $request->filled('category_id')) {
                $query->where('category_id', $request->get('category', $request->get('category_id')));
            }

            if ($request->filled('store_id')) {
                $query->where('store_id', $request->store_id);
            }

            if ($request->filled('status')) {
                $status = $this->normalizeStatus($request->status);
                if ($status !== null) {
                    $query->where('status', $status);
                }
            }

            $sort = $request->get('sort', 'recent');
            match ($sort) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                default      => $query->latest(),
            };

            $paginated = $query->paginate($perPage);
            $paginated->getCollection()->transform(function ($item) {
                return $this->withImageUrl($item);
            });

            return response()->json($paginated);
        } catch (Throwable $e) {
            Log::error('Public products listing failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json($this->emptyPagination($perPage));
        }
    }

    /**
     * Crear producto (AUTENTICADO)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:draft,active,0,1,true,false',
            'image'       => 'nullable|image|max:2048',
        ]);

        // ????????? Obtener tienda del usuario autenticado
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        $data['store_id'] = $store->id;
        $data['user_id']  = $request->user()->id;

        if (array_key_exists('status', $data)) {
            $normalized = $this->normalizeStatus($data['status']);
            if ($normalized !== null) {
                $data['status'] = $normalized;
            } else {
                unset($data['status']);
            }
        }

        // Evitar error NOT NULL
        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        // Generar slug ??nico si no viene
        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 1;

            while (Product::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $data['slug'] = $slug;
        }

        // Cargar imagen si se env??a
        if ($request->hasFile('image')) {
            $upload = $this->mediaUploader->uploadImage($request->file('image'), 'comercioplus/products');
            $data['image_path'] = $upload['path'];
            $data['image_url'] = $upload['url'];
            $data['image'] = $upload['url'];
        }

        $product = Product::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
        ], 201);
    }

    /**
     * Mostrar producto p??blico
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => 'ok',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
        ]);
    }

    /**
     * Actualizar producto (SOLO PROPIETARIO)
     */
    public function update(Request $request, Product $product)
    {
        // ????????? Seguridad: solo due??o
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|nullable|string|max:255|unique:products,slug,' . $product->id,
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'sometimes|nullable|string',
            'status'      => 'sometimes|in:draft,active,0,1,true,false',
            'image'       => 'sometimes|nullable|image|max:2048',
        ]);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        if (array_key_exists('status', $data)) {
            $normalized = $this->normalizeStatus($data['status']);
            if ($normalized !== null) {
                $data['status'] = $normalized;
            } else {
                unset($data['status']);
            }
        }

        // Regenerar slug si viene vac??o
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? $product->name);
            $slug = $base;
            $i = 1;

            while (
                Product::where('slug', $slug)
                    ->where('id', '!=', $product->id)
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }

            $data['slug'] = $slug;
        }

        // Reemplazar imagen si viene
        if ($request->hasFile('image')) {
            $this->deleteLocalFileIfNeeded($product->image_path);
            $upload = $this->mediaUploader->uploadImage($request->file('image'), 'comercioplus/products');
            $data['image_path'] = $upload['path'];
            $data['image_url'] = $upload['url'];
            $data['image'] = $upload['url'];
        }

        $product->update($data);

        return response()->json([
            'status' => 'updated',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
        ]);
    }

    /**
     * Eliminar producto (SOLO PROPIETARIO)
     */
    public function destroy(Request $request, Product $product)
    {
        // ????????? Seguridad: solo due??o
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $this->deleteLocalFileIfNeeded($product->image_path);

        $product->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Producto eliminado correctamente',
        ]);
    }

    private function withImageUrl(Product $product): Product
    {
        $product->image_url = $this->resolveMediaUrl($product->image_url, $product->image_path ?: $product->image);

        if (isset($product->status)) {
            $product->status = $product->status ? 'active' : 'draft';
        }

        return $product;
    }

    private function resolveMediaUrl(?string $explicitUrl, ?string $path): ?string
    {
        if ($this->mediaUploader->isAbsoluteUrl($explicitUrl)) {
            return $explicitUrl;
        }

        if ($this->mediaUploader->isAbsoluteUrl($path)) {
            return $path;
        }

        if ($path) {
            return Storage::disk('public')->url($path);
        }

        return $explicitUrl ?: null;
    }

    private function deleteLocalFileIfNeeded(?string $path): void
    {
        if (!$path || $this->mediaUploader->isAbsoluteUrl($path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function emptyPagination(int $perPage): array
    {
        return [
            'current_page' => 1,
            'data' => [],
            'last_page' => 1,
            'per_page' => $perPage,
            'total' => 0,
        ];
    }

    private function normalizeStatus($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        $value = strtolower((string) $value);
        if (in_array($value, ['active', '1', 'true', 'on'], true)) {
            return 1;
        }
        if (in_array($value, ['draft', '0', 'false', 'off'], true)) {
            return 0;
        }
        return null;
    }
}

