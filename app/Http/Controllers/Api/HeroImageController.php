<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class HeroImageController extends Controller
{
    public function index(): JsonResponse
    {
        $path = public_path('hero');
        $images = [];

        if (File::exists($path)) {
            $files = File::files($path);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $images[] = url('/hero/' . $file->getFilename());
                }
            }
        }

        return response()->json([
            'images' => $images,
        ]);
    }
}
