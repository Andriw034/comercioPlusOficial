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

    public function index(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get()->map(fn ($store) => $this->withMediaUrls($store));

        return response()->json($stores);
    }

    public function myStore(Request $request)
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Tienda no encontrada'], 404);
        }

        return response()->json($this->withMediaUrls($store));
    }

    public function publicStores()
    {
        $stores = Store::where('is_visible', true)->get()->map(fn ($store) => $this->withMediaUrls($store));

        return response()->json($stores);
    }

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
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cover'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
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

        return response()->json([
            'success' => true,
            'message' => 'Tienda creada correctamente',
            'data' => $this->withMediaUrls($store),
        ], 201);
    }

    public function show(Store $store)
    {
        if (!$store->is_visible && auth()->check() && $store->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        return response()->json($this->withMediaUrls($store));
    }

    public function update(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
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
            'logo'        => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cover'       => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $store->update($data);
        $this->handleMedia($request, $store);

        return response()->json([
            'success' => true,
            'message' => 'Tienda actualizada correctamente',
            'data' => $this->withMediaUrls($store),
        ]);
    }

    public function uploadLogo(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $this->replaceLogo($store, $request->file('logo'));

        return response()->json([
            'success' => true,
            'message' => 'Logo actualizado correctamente',
            'data' => $this->withMediaUrls($store->fresh()),
        ]);
    }

    public function uploadCover(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $this->replaceCover($store, $request->file('cover'));

        return response()->json([
            'success' => true,
            'message' => 'Portada actualizada correctamente',
            'data' => $this->withMediaUrls($store->fresh()),
        ]);
    }

    public function destroy(Request $request, Store $store)
    {
        if ($store->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($store->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la tienda porque tiene productos asociados'
            ], 422);
        }

        $this->mediaUploader->deleteImage($store->logo_public_id ?: $store->logo_path);
        $this->mediaUploader->deleteImage($store->cover_public_id ?: $store->cover_path ?: $store->background_path);

        $store->delete();

        return response()->json(['success' => true, 'message' => 'Tienda eliminada correctamente']);
    }

    private function handleMedia(Request $request, Store $store): void
    {
        if ($request->hasFile('logo')) {
            $this->replaceLogo($store, $request->file('logo'));
        }

        if ($request->hasFile('cover')) {
            $this->replaceCover($store, $request->file('cover'));
        }

        $this->withMediaUrls($store);
    }

    private function replaceLogo(Store $store, $logo): void
    {
        $this->mediaUploader->deleteImage($store->logo_public_id ?: $store->logo_path);
        $this->deleteLocalFileIfNeeded($store->logo_path);

        $upload = $this->mediaUploader->uploadImage($logo, "stores/{$store->id}/logo");
        $store->logo_path = $upload['path'];
        $store->logo_public_id = $upload['path'];
        $store->logo_url = $upload['url'];
        $store->save();
    }

    private function replaceCover(Store $store, $cover): void
    {
        $this->mediaUploader->deleteImage($store->cover_public_id ?: $store->cover_path ?: $store->background_path);
        $this->deleteLocalFileIfNeeded($store->cover_path);
        $this->deleteLocalFileIfNeeded($store->background_path);

        $upload = $this->mediaUploader->uploadImage($cover, "stores/{$store->id}/cover");
        $store->cover_path = $upload['path'];
        $store->background_path = $upload['path'];
        $store->cover_public_id = $upload['path'];
        $store->cover_url = $upload['url'];
        $store->background_url = $upload['url'];
        $store->save();
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
