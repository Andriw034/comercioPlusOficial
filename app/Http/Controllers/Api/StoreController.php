<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Support\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function __construct(private readonly MediaUploader $mediaUploader)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Private stores (owner)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get()->map(fn ($store) => $this->withMediaUrls($store));

        return response()->json($stores);
    }

    /**
     * Obtener mi tienda (merchant)
     */
    public function myStore(Request $request)
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        return response()->json($this->withMediaUrls($store));
    }

    /*
    |--------------------------------------------------------------------------
    | Public stores
    |--------------------------------------------------------------------------
    */
    public function publicStores()
    {
        $stores = Store::where('is_visible', true)->get()->map(fn ($store) => $this->withMediaUrls($store));

        return response()->json($stores);
    }

    /*
    |--------------------------------------------------------------------------
    | Create store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:stores,slug',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string|max:50',
            'whatsapp'    => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'facebook'    => 'nullable|string|max:255',
            'instagram'   => 'nullable|string|max:255',
            'address'     => 'nullable|string|max:500',
            'is_visible'  => 'nullable|boolean',
            'logo'        => 'nullable|image|max:2048',
            'cover'       => 'nullable|image|max:4096',
        ]);

        $data['slug'] = isset($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $store = Store::create([
            'user_id'     => $request->user()->id,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'whatsapp'    => $data['whatsapp'] ?? null,
            'support_email' => $data['support_email'] ?? null,
            'facebook'    => $data['facebook'] ?? null,
            'instagram'   => $data['instagram'] ?? null,
            'address'     => $data['address'] ?? null,
            'is_visible'  => $data['is_visible'] ?? true,
        ]);

        $this->handleMedia($request, $store);

        return response()->json($this->withMediaUrls($store), 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Show store (public or owner via route)
    |--------------------------------------------------------------------------
    */
    public function show(Store $store)
    {
        if (!$store->is_visible && auth()->check() && $store->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($this->withMediaUrls($store));
    }

    /*
    |--------------------------------------------------------------------------
    | Update store
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:stores,slug,' . $store->id,
            'description' => 'nullable|string',
            'phone'       => 'nullable|string|max:50',
            'whatsapp'    => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'facebook'    => 'nullable|string|max:255',
            'instagram'   => 'nullable|string|max:255',
            'address'     => 'nullable|string|max:500',
            'is_visible'  => 'sometimes|boolean',
        ]);

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $store->update($data);
        $this->handleMedia($request, $store);

        return response()->json($this->withMediaUrls($store));
    }

    /*
    |--------------------------------------------------------------------------
    | Delete store
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($store->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la tienda porque tiene productos asociados'
            ], 422);
        }

        $store->delete();

        return response()->json(null, 204);
    }

    private function handleMedia(Request $request, Store $store): void
    {
        if ($request->hasFile('logo')) {
            $this->deleteLocalFileIfNeeded($store->logo_path);
            $upload = $this->mediaUploader->uploadImage($request->file('logo'), 'comercioplus/stores/logos');
            $store->logo_path = $upload['path'];
            $store->logo_url = $upload['url'];
        }

        if ($request->hasFile('cover')) {
            $this->deleteLocalFileIfNeeded($store->cover_path);
            $this->deleteLocalFileIfNeeded($store->background_path);

            $upload = $this->mediaUploader->uploadImage($request->file('cover'), 'comercioplus/stores/covers');
            $store->cover_path = $upload['path'];
            $store->background_path = $upload['path'];
            $store->cover_url = $upload['url'];
            $store->background_url = $upload['url'];
        }

        $store->save();
        $this->withMediaUrls($store);
    }

    private function withMediaUrls(Store $store): Store
    {
        $store->logo_url = $this->resolveMediaUrl($store->logo_url, $store->logo_path);
        $store->cover_url = $this->resolveMediaUrl($store->cover_url ?: $store->background_url, $store->cover_path ?: $store->background_path);
        $store->background_url = $store->cover_url ?: $store->background_url;

        return $store;
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
}
