<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PexelsApi
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.pexels.base_url', 'https://api.pexels.com/v1/'), '/') . '/';
        $this->apiKey  = (string) config('services.pexels.key', '');
    }

    /**
     * Busca imágenes en Pexels.
     * - $query: términos (ej: "motorcycle parts", "motorcycle accessories", "helmets").
     * - $limit: cantidad de resultados (1-80).
     * - $ttl: segundos de caché (por defecto 600s = 10min).
     */
    public function search(string $query = 'motorcycle parts', int $limit = 12, int $ttl = 600): array
    {
        $query = trim($query);
        $limit = max(1, min(80, $limit));

        $key = "pexels:search:q=" . md5($query) . ":l={$limit}";

        return Cache::remember($key, $ttl, function () use ($query, $limit) {
            $response = Http::timeout(12)
                ->retry(2, 500)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ])->get($this->baseUrl . 'search', [
                    'query'   => $query,
                    'per_page'=> $limit,
                ]);

            if ($response->failed()) {
                // devuelve formato estable (evita romper la UI)
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'data' => [],
                    'message' => $response->json('error') ?? 'Error al consultar Pexels',
                ];
            }

            $photos = (array) $response->json('photos', []);
            // Normalizar a un formato compacto para la UI
            $normalized = array_map(function ($p) {
                return [
                    'id'         => $p['id'] ?? null,
                    'alt'        => $p['alt'] ?? 'Imagen',
                    'photographer'=> $p['photographer'] ?? null,
                    'src'        => [
                        'tiny'   => $p['src']['tiny']   ?? null,
                        'small'  => $p['src']['small']  ?? null,
                        'medium' => $p['src']['medium'] ?? null,
                        'large'  => $p['src']['large']  ?? null,
                    ],
                    'link'       => $p['url'] ?? null,
                ];
            }, $photos);

            return [
                'error' => false,
                'status' => 200,
                'data' => $normalized,
                'message' => null,
            ];
        });
    }
}
