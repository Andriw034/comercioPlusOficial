<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCode;
use App\Models\Store;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinaryService)
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
                ->with(['category', 'store', 'productCodes']);

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
            'image'       => 'nullable|file|max:5120|mimetypes:image/jpeg,image/png,image/webp,image/avif',
            'image_url'   => 'nullable|url|max:2048',
            'codes' => 'nullable|array|max:10',
            'codes.*.type' => 'required_with:codes|in:barcode,qr,sku',
            'codes.*.value' => 'required_with:codes|string|max:191',
            'codes.*.is_primary' => 'sometimes|boolean',
            'primary_code_type' => 'nullable|in:barcode,qr,sku',
            'primary_code_value' => 'nullable|string|max:191',
        ]);

        // ????????? Obtener tienda del usuario autenticado
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        $data['store_id'] = $store->id;
        $data['user_id']  = $request->user()->id;

        $incomingCodes = $this->normalizeIncomingCodes($request);
        $this->assertUniqueCodesForStore(
            storeId: (int) $store->id,
            codes: $incomingCodes,
        );

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
            $upload = $this->cloudinaryService->uploadImage($request->file('image'), 'comercio-plus/products');
            $data['image_path'] = $upload['path'];
            $data['image_url'] = $upload['url'];
            $data['image'] = $upload['url'];
        } elseif (!empty($data['image_url'])) {
            $data['image'] = $data['image_url'];
        }

        $product = DB::transaction(function () use ($data, $incomingCodes, $store) {
            $product = Product::create($data);
            $this->syncProductCodes($product, (int) $store->id, $incomingCodes, true);
            return $product;
        });

        Cache::increment('public_products_version');

        return response()->json([
            'status' => 'created',
            'data'   => $this->withImageUrl($product->load('category', 'store', 'productCodes')),
        ], 201);
    }

    /**
     * Mostrar producto p??blico
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => 'ok',
            'data'   => $this->withImageUrl($product->load('category', 'store', 'productCodes')),
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
            'image'       => 'sometimes|nullable|file|max:5120|mimetypes:image/jpeg,image/png,image/webp,image/avif',
            'image_url'   => 'sometimes|nullable|url|max:2048',
            'codes' => 'sometimes|array|max:10',
            'codes.*.type' => 'required_with:codes|in:barcode,qr,sku',
            'codes.*.value' => 'required_with:codes|string|max:191',
            'codes.*.is_primary' => 'sometimes|boolean',
            'primary_code_type' => 'nullable|in:barcode,qr,sku',
            'primary_code_value' => 'nullable|string|max:191',
        ]);

        $codesProvided = $request->has('codes')
            || $request->filled('primary_code_value')
            || $request->filled('primary_code_type');
        $incomingCodes = $codesProvided ? $this->normalizeIncomingCodes($request) : null;

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
            $upload = $this->cloudinaryService->uploadImage($request->file('image'), 'comercio-plus/products');
            $data['image_path'] = $upload['path'];
            $data['image_url'] = $upload['url'];
            $data['image'] = $upload['url'];
        } elseif (array_key_exists('image_url', $data) && !empty($data['image_url'])) {
            $this->deleteLocalFileIfNeeded($product->image_path);
            $data['image_path'] = null;
            $data['image'] = $data['image_url'];
        }

        if ($codesProvided) {
            $this->assertUniqueCodesForStore(
                storeId: (int) $product->store_id,
                codes: $incomingCodes ?? [],
                exceptProductId: (int) $product->id,
            );
        }

        DB::transaction(function () use ($product, $data, $codesProvided, $incomingCodes) {
            $product->update($data);
            if ($codesProvided) {
                $this->syncProductCodes($product, (int) $product->store_id, $incomingCodes ?? [], true);
            }
        });

        Cache::increment('public_products_version');

        return response()->json([
            'status' => 'updated',
            'data'   => $this->withImageUrl($product->fresh()->load('category', 'store', 'productCodes')),
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
        Cache::increment('public_products_version');

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
        if ($this->cloudinaryService->isAbsoluteUrl($explicitUrl)) {
            return $explicitUrl;
        }

        if ($this->cloudinaryService->isAbsoluteUrl($path)) {
            return $path;
        }

        if ($path) {
            return Storage::disk('public')->url($path);
        }

        return $explicitUrl ?: null;
    }

    private function deleteLocalFileIfNeeded(?string $path): void
    {
        if (!$path || $this->cloudinaryService->isAbsoluteUrl($path)) {
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

    private function normalizeIncomingCodes(Request $request): array
    {
        $rawCodes = $request->input('codes', []);
        if (!is_array($rawCodes)) {
            $rawCodes = [];
        }

        $primaryType = $request->input('primary_code_type');
        $primaryValue = trim((string) $request->input('primary_code_value', ''));

        if ($primaryValue !== '') {
            $rawCodes[] = [
                'type' => $primaryType ?: 'barcode',
                'value' => $primaryValue,
                'is_primary' => true,
            ];
        }

        $normalized = [];
        $seen = [];

        foreach ($rawCodes as $rawCode) {
            if (!is_array($rawCode)) {
                continue;
            }

            $type = strtolower(trim((string) ($rawCode['type'] ?? '')));
            $value = trim((string) ($rawCode['value'] ?? ''));
            $isPrimary = (bool) ($rawCode['is_primary'] ?? false);

            if (!in_array($type, ['barcode', 'qr', 'sku'], true)) {
                continue;
            }

            if ($value === '') {
                continue;
            }

            $key = $type . '|' . mb_strtolower($value);
            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    'codes' => ["Codigo duplicado en la solicitud: {$type} {$value}."],
                ]);
            }
            $seen[$key] = true;

            $normalized[] = [
                'type' => $type,
                'value' => $value,
                'is_primary' => $isPrimary,
            ];
        }

        if ($normalized !== []) {
            $hasPrimary = collect($normalized)->contains(fn (array $code) => $code['is_primary'] === true);
            if (! $hasPrimary) {
                $normalized[0]['is_primary'] = true;
            } else {
                $primaryAssigned = false;
                foreach ($normalized as $index => $code) {
                    if ($code['is_primary'] && ! $primaryAssigned) {
                        $primaryAssigned = true;
                        continue;
                    }
                    if ($code['is_primary']) {
                        $normalized[$index]['is_primary'] = false;
                    }
                }
            }
        }

        return $normalized;
    }

    private function assertUniqueCodesForStore(int $storeId, array $codes, ?int $exceptProductId = null): void
    {
        if ($codes === []) {
            return;
        }

        $query = ProductCode::query()
            ->where('store_id', $storeId)
            ->where(function ($where) use ($codes) {
                foreach ($codes as $code) {
                    $where->orWhere(function ($row) use ($code) {
                        $row
                            ->where('type', $code['type'])
                            ->where('value', $code['value']);
                    });
                }
            });

        if ($exceptProductId !== null) {
            $query->where('product_id', '!=', $exceptProductId);
        }

        $conflict = $query->first();
        if ($conflict) {
            throw ValidationException::withMessages([
                'codes' => ["El codigo {$conflict->value} ({$conflict->type}) ya existe en otro producto de tu tienda."],
            ]);
        }
    }

    private function syncProductCodes(Product $product, int $storeId, array $codes, bool $replace): void
    {
        if ($replace) {
            ProductCode::query()
                ->where('product_id', (int) $product->id)
                ->where('store_id', $storeId)
                ->delete();
        }

        if ($codes === []) {
            return;
        }

        foreach ($codes as $code) {
            ProductCode::query()->create([
                'product_id' => (int) $product->id,
                'store_id' => $storeId,
                'type' => $code['type'],
                'value' => $code['value'],
                'is_primary' => (bool) ($code['is_primary'] ?? false),
            ]);
        }
    }
}

