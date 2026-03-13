<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreVerification;
use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class StoreVerificationController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinaryService)
    {
    }

    public function show(Request $request)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $verification = StoreVerification::query()
            ->where('store_id', $store->id)
            ->first();

        if (! $verification) {
            return response()->json([
                'status' => 'ok',
                'data' => null,
                'store_is_verified' => false,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $verification,
            'store_is_verified' => (bool) $store->is_verified,
        ]);
    }

    public function submit(Request $request)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((bool) $store->is_verified) {
            return response()->json([
                'message' => 'Tu tienda ya está verificada',
            ], 422);
        }

        $pending = StoreVerification::query()
            ->where('store_id', $store->id)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return response()->json([
                'message' => 'Ya tienes una solicitud de verificación en revisión',
            ], 422);
        }

        $validated = $request->validate([
            'document' => 'required|file|max:5120|mimetypes:application/pdf,image/jpeg,image/png,image/webp',
        ]);

        $upload = $this->cloudinaryService->uploadImage($validated['document'], 'comercio-plus/verifications');

        $verification = StoreVerification::query()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'status' => 'pending',
                'document_url' => $upload['url'],
                'document_path' => $upload['path'],
                'notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        return response()->json([
            'status' => 'ok',
            'message' => 'Solicitud de verificación enviada',
            'data' => $verification,
        ], 201);
    }

    private function resolveMerchantStore(Request $request): ?Store
    {
        try {
            return Store::query()->where('user_id', $request->user()->id)->firstOrFail();
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}
