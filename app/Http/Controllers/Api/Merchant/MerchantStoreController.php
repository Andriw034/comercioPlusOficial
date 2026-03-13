<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MerchantStoreController extends Controller
{
    /**
     * GET /api/merchant/store
     * Devuelve la tienda del comerciante o un payload vacio si aun no existe.
     */
    public function show(Request $request): JsonResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return response()->json([
                'data' => $this->emptyStoreData(),
                'meta' => ['exists' => false],
            ]);
        }

        return response()->json([
            'data' => $this->storeData($store),
            'meta' => ['exists' => true],
        ]);
    }

    /**
     * PUT /api/merchant/store
     * Actualiza la tienda del comerciante; si no existe, la crea.
     */
    public function update(Request $request): JsonResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'category'          => ['nullable', 'string', 'max:120'],
            'city'              => ['nullable', 'string', 'max:120'],
            'address'           => ['nullable', 'string', 'max:500'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'whatsapp'          => ['nullable', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:255'],
            'schedule'          => ['nullable', 'string', 'max:1000'],
            'logo_url'          => ['nullable', 'url', 'max:500'],
            'cover_url'         => ['nullable', 'url', 'max:500'],
            'currency'          => ['nullable', 'string', 'max:10'],
            'taxes_enabled'     => ['nullable', 'boolean'],
            'payment_methods'   => ['nullable', 'array'],
            'payment_methods.*' => ['string', 'max:50'],
            'is_public'         => ['nullable', 'boolean'],
        ]);

        if (! $store) {
            $store = Store::create([
                'user_id'         => $request->user()->id,
                'name'            => $validated['name'],
                'slug'            => $this->generateUniqueSlug($validated['name']),
                'description'     => $validated['description'] ?? null,
                'category'        => $validated['category'] ?? null,
                'city'            => $validated['city'] ?? null,
                'address'         => $validated['address'] ?? null,
                'phone'           => $validated['phone'] ?? null,
                'whatsapp'        => $validated['whatsapp'] ?? null,
                'support_email'   => $validated['email'] ?? null,
                'schedule'        => $validated['schedule'] ?? null,
                'logo_url'        => $validated['logo_url'] ?? null,
                'cover_url'       => $validated['cover_url'] ?? null,
                'background_url'  => $validated['cover_url'] ?? null,
                'currency'        => $validated['currency'] ?? 'COP',
                'taxes_enabled'   => $validated['taxes_enabled'] ?? false,
                'payment_methods' => $validated['payment_methods'] ?? [],
                'is_visible'      => $validated['is_public'] ?? true,
            ]);
        } else {
            $store->update([
                'name'            => $validated['name'],
                'description'     => $validated['description'] ?? $store->description,
                'category'        => $validated['category'] ?? $store->category,
                'city'            => $validated['city'] ?? $store->city,
                'address'         => $validated['address'] ?? $store->address,
                'phone'           => $validated['phone'] ?? $store->phone,
                'whatsapp'        => $validated['whatsapp'] ?? $store->whatsapp,
                'support_email'   => $validated['email'] ?? $store->support_email,
                'schedule'        => $validated['schedule'] ?? $store->schedule,
                'logo_url'        => $validated['logo_url'] ?? $store->logo_url,
                'cover_url'       => $validated['cover_url'] ?? $store->cover_url,
                'background_url'  => $validated['cover_url'] ?? $store->background_url,
                'currency'        => $validated['currency'] ?? $store->currency ?? 'COP',
                'taxes_enabled'   => $validated['taxes_enabled'] ?? $store->taxes_enabled ?? false,
                'payment_methods' => $validated['payment_methods'] ?? $store->payment_methods ?? [],
                'is_visible'      => $validated['is_public'] ?? $store->is_visible ?? true,
            ]);
        }

        return response()->json([
            'data' => $this->storeData($store->fresh()),
            'meta' => ['exists' => true],
        ]);
    }

    private function emptyStoreData(): array
    {
        return [
            'id'              => null,
            'slug'            => '',
            'name'            => '',
            'description'     => '',
            'category'        => '',
            'city'            => '',
            'address'         => '',
            'phone'           => '',
            'whatsapp'        => '',
            'email'           => '',
            'schedule'        => '',
            'logo_url'        => '',
            'cover_url'       => '',
            'currency'        => 'COP',
            'taxes_enabled'   => false,
            'payment_methods' => [],
            'is_public'       => false,
        ];
    }

    private function storeData(Store $store): array
    {
        return [
            'id'              => (int) $store->id,
            'slug'            => (string) ($store->slug ?? ''),
            'name'            => (string) ($store->name ?? ''),
            'description'     => (string) ($store->description ?? ''),
            'category'        => (string) ($store->category ?? ''),
            'city'            => (string) ($store->city ?? ''),
            'address'         => (string) ($store->address ?? ''),
            'phone'           => (string) ($store->phone ?? ''),
            'whatsapp'        => (string) ($store->whatsapp ?? ''),
            'email'           => (string) ($store->support_email ?? ''),
            'schedule'        => (string) ($store->schedule ?? ''),
            'logo_url'        => (string) ($store->logo_url ?? ''),
            'cover_url'       => (string) ($store->cover_url ?? ''),
            'currency'        => (string) ($store->currency ?? 'COP'),
            'taxes_enabled'   => (bool) ($store->taxes_enabled ?? false),
            'payment_methods' => is_array($store->payment_methods) ? $store->payment_methods : [],
            'is_public'       => (bool) ($store->is_visible ?? false),
        ];
    }

    private function generateUniqueSlug(string $source): string
    {
        $baseSlug = Str::slug($source);
        if ($baseSlug === '') {
            $baseSlug = 'tienda';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Store::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
