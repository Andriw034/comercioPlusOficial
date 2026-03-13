<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UploadController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinaryService)
    {
    }

    public function storeProductImage(Request $request): JsonResponse
    {
        return $this->upload($request, 'comercio-plus/products');
    }

    public function storeStoreLogo(Request $request): JsonResponse
    {
        return $this->upload($request, 'comercio-plus/stores/logo');
    }

    public function storeStoreCover(Request $request): JsonResponse
    {
        return $this->upload($request, 'comercio-plus/stores/cover');
    }

    public function storeProfilePhoto(Request $request): JsonResponse
    {
        $response = $this->upload($request, 'comercio-plus/profiles');
        $payload = $response->getData(true);

        if (!isset($payload['data']['url'])) {
            return $response;
        }

        $url = (string) $payload['data']['url'];
        $user = $request->user();

        if ($user) {
            if (Schema::hasColumn('users', 'avatar_path')) {
                $user->avatar_path = $url;
            }

            if (Schema::hasColumn('users', 'avatar')) {
                $user->setAttribute('avatar', $url);
            }

            $user->save();

            if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'image')) {
                $profile = Profile::query()->where('user_id', $user->id)->first();
                if ($profile) {
                    $profile->image = $url;
                    $profile->save();
                }
            }
        }

        return $response;
    }

    private function upload(Request $request, string $folder): JsonResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'max:5120',
                'mimetypes:image/jpeg,image/png,image/webp,image/avif',
            ],
        ]);

        try {
            $result = $this->cloudinaryService->uploadImage($validated['image'], $folder);
        } catch (Throwable $e) {
            Log::error('Upload failed', [
                'folder' => $folder,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => 'No se pudo subir la imagen.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => [
                'url' => $result['url'],
                'public_id' => $result['public_id'],
                'width' => $result['width'],
                'height' => $result['height'],
            ],
            'message' => 'Uploaded',
        ]);
    }
}

