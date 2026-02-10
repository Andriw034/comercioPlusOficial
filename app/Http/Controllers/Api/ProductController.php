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

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $perPage = ($perPage > 0 && $perPage <= 50) ? $perPage : 12;

        try {
            if (!Schema::hasTable('products')) {
                return response()->json($this->emptyPagination($perPage));
            }

            $query = Product::query()->included()->with(['category', 'store']);

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
            $paginated->getCollection()->transform(fn ($item) => $this->withImageUrl($item));

            return response()->json($paginated);
        } catch (Throwable $e) {
            Log::error('Public products listing failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json($this->emptyPagination($perPage));
        }
    }

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
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

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

        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name']);
        }

        $product = Product::create($data);

        $this->handleProductImages($request, $product);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data'   => $this->withImageUrl($product->fresh()->load('category', 'store')),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'message' => 'Producto obtenido correctamente',
            'data'   => $this->withImageUrl($product->load('category', 'store')),
        ]);
    }

    public function update(Request $request, Product $product)
    {
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
            'image'       => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images'      => 'sometimes|nullable|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:5120',
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

        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name'] ?? $product->name, $product->id);
        }

        $product->update($data);
        $this->handleProductImages($request, $product);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'data'   => $this->withImageUrl($product->fresh()->load('category', 'store')),
        ]);
    }

    public function updateImage(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $this->replacePrimaryImage($product, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Imagen de producto actualizada correctamente',
            'data' => $this->withImageUrl($product->fresh()->load('category', 'store')),
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $this->deletePreviousImages($product);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
        ]);
    }

    private function handleProductImages(Request $request, Product $product): void
    {
        if ($request->hasFile('image')) {
            $this->replacePrimaryImage($product, $request->file('image'));
        }

        if ($request->hasFile('images')) {
            $uploads = [];
            foreach ($request->file('images') as $file) {
                $upload = $this->mediaUploader->uploadImage($file, "stores/{$product->store_id}/products/{$product->id}");
                $uploads[] = [
                    'url' => $upload['url'],
                    'public_id' => $upload['path'],
                ];
            }

            if (!empty($uploads)) {
                $product->image_urls = array_values(array_map(fn ($item) => $item['url'], $uploads));
                if (!$product->image_url) {
                    $product->image_url = $uploads[0]['url'];
                    $product->image = $uploads[0]['url'];
                    $product->image_path = $uploads[0]['public_id'];
                    $product->image_public_id = $uploads[0]['public_id'];
                }
                $product->save();
            }
        }
    }

    private function replacePrimaryImage(Product $product, $file): void
    {
        $this->mediaUploader->deleteImage($product->image_public_id ?: $product->image_path);
        $this->deleteLocalFileIfNeeded($product->image_path);

        $upload = $this->mediaUploader->uploadImage($file, "stores/{$product->store_id}/products/{$product->id}");
        $product->image_path = $upload['path'];
        $product->image_public_id = $upload['path'];
        $product->image_url = $upload['url'];
        $product->image = $upload['url'];

        $existing = is_array($product->image_urls) ? $product->image_urls : [];
        array_unshift($existing, $upload['url']);
        $product->image_urls = array_values(array_unique($existing));
        $product->save();
    }

    private function deletePreviousImages(Product $product): void
    {
        $this->mediaUploader->deleteImage($product->image_public_id ?: $product->image_path);
        $this->deleteLocalFileIfNeeded($product->image_path);
    }

    private function withImageUrl(Product $product): Product
    {
        $product->image_url = $this->resolveMediaUrl($product->image_url, $product->image_path ?: $product->image);
        $product->image_urls = collect($product->image_urls ?? [])
            ->map(fn ($url) => $this->resolveMediaUrl($url, $url))
            ->filter()
            ->values()
            ->all();

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

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
