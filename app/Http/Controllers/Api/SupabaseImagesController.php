<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseImagesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = $request->validate([
                'q'     => ['nullable', 'string'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $q = $data['q'] ?? '';          // permite vacío
            $limit = $data['limit'] ?? 6;   // default 6

            $url   = env('SUPABASE_URL');
            $key   = env('SUPABASE_ANON_KEY');
            $table = env('SUPABASE_TABLE', 'images'); // Cambiado a 'images' basado en logs

            if ($url && $key && $table) {
                $params = [
                    'select' => 'id,title,file_path,public_url,created_at',
                    'limit'  => $limit,
                ];

                if ($q !== '') {
                    $params['title'] = "ilike.%{$q}%";
                }

                $resp = Http::withHeaders([
                    'apikey'        => $key,
                    'Authorization' => "Bearer {$key}",
                ])->get("{$url}/rest/v1/{$table}", $params);

                if ($resp->ok()) {
                    $rows = $resp->json() ?: [];

                    $data = collect($rows)->map(function ($row) use ($url) {
                        $id        = $row['id']         ?? null;
                        $title     = $row['title']      ?? 'Sin título';
                        $filePath  = $row['file_path']  ?? '';
                        $publicUrl = $row['public_url'] ?? '';
                        $created   = $row['created_at'] ?? null;

                        $finalUrl = null;

                        if (is_string($filePath) && str_starts_with($filePath, 'http')) {
                            $finalUrl = $filePath;
                        } elseif (is_string($publicUrl) && str_starts_with($publicUrl, 'http')) {
                            $finalUrl = $publicUrl;
                        } elseif ($filePath) {
                            $finalUrl = rtrim($url, '/') . '/storage/v1/object/public/' . ltrim($filePath, '/');
                        }

                        return [
                            'id'         => $id,
                            'title'      => $title,
                            'url'        => $finalUrl,
                            'created_at' => $created,
                        ];
                    })
                    ->filter(fn ($r) => !empty($r['url']))
                    ->values();

                    return response()->json([
                        'ok'   => true,
                        'data' => $data,
                    ]);
                } else {
                    Log::warning('Supabase API error, falling back to local products', [
                        'status' => $resp->status(),
                        'body' => $resp->body(),
                    ]);
                }
            }

            $items = \App\Models\Product::query()
                ->latest('id')
                ->take($limit)
                ->get()
                ->map(function ($p) {
                    return [
                        'id'         => (string) $p->id,
                        'title'      => $p->name,
                        'url'        => $p->image_url ?: asset('images/placeholder.jpg'),
                        'created_at' => optional($p->created_at)->toISOString(),
                    ];
                })
                ->values();

            return response()->json([
                'ok'   => true,
                'data' => $items,
            ]);

        } catch (\Throwable $e) {
            Log::error('supabase-images error', [
                'e' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'error' => 'internal_error',
                'message' => 'Revisa storage/logs/laravel.log',
            ], 500);
        }
    }
}
