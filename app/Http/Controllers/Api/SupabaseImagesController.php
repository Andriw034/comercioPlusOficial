<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseImagesController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        $q     = trim((string) $request->query('q', ''));

        $supabaseUrl = rtrim(env('SUPABASE_URL', ''), '/');
        $anonKey     = env('SUPABASE_ANON_KEY', '');
        $serviceKey  = env('SUPABASE_SERVICE_ROLE_KEY', '');
        $table       = env('SUPABASE_TABLE', 'product');
        $bucket      = env('SUPABASE_PUBLIC_BUCKET', 'moto_images');

        if (!$supabaseUrl || (!$anonKey && !$serviceKey) || !$table) {
            return response()->json([
                'source' => 'local-fallback',
                'data'   => $this->fallbackLocal($limit),
            ]);
        }

        $bearerKey = $serviceKey ?: $anonKey; // usa service role si existe

        try {
            $select = 'id,name,image,image_path,public_url,price,price_cents,created_at';
            $base   = "{$supabaseUrl}/rest/v1/{$table}?select={$select}";

            $filters = [];
            if ($q !== '') {
                $filters[] = 'name=ilike.*' . rawurlencode($q) . '*';
            }
            $filters[] = 'or=(image.not.is.null,image_path.not.is.null,public_url.not.is.null)';

            $query = $base
                . (count($filters) ? ('&' . implode('&', $filters)) : '')
                . "&order=created_at.desc&limit={$limit}";

            $resp = Http::withHeaders([
                'apikey'        => $bearerKey,
                'Authorization' => 'Bearer ' . $bearerKey,
            ])->get($query);

            if (!$resp->ok()) {
                return response()->json([
                    'source' => 'local-fallback',
                    'error'  => 'supabase_http_error',
                    'data'   => $this->fallbackLocal($limit),
                ]);
            }

            $rows = $resp->json();
            if (!is_array($rows)) {
                return response()->json([
                    'source' => 'local-fallback',
                    'error'  => 'supabase_bad_json',
                    'data'   => $this->fallbackLocal($limit),
                ]);
            }

            $items = [];
            foreach ($rows as $row) {
                $norm = $this->normalizeSupabaseRow($row, $supabaseUrl, $bucket);
                if ($norm) $items[] = $norm;
            }

            if (empty($items)) {
                return response()->json([
                    'source' => 'local-fallback',
                    'error'  => 'supabase_empty_after_map',
                    'data'   => $this->fallbackLocal($limit),
                ]);
            }

            return response()->json([
                'source' => 'supabase',
                'data'   => $items,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'source' => 'local-fallback',
                'error'  => 'exception:' . $e->getMessage(),
                'data'   => $this->fallbackLocal($limit),
            ]);
        }
    }

    protected function normalizeSupabaseRow(array $row, string $supabaseUrl, string $bucket): ?array
    {
        $id        = Arr::get($row, 'id');
        $name      = Arr::get($row, 'name');
        $image     = Arr::get($row, 'image');
        $imagePath = Arr::get($row, 'image_path');
        $publicUrl = Arr::get($row, 'public_url');
        $createdAt = Arr::get($row, 'created_at');
        $price     = $this->normalizePrice(Arr::get($row, 'price'));

        if ($price === null) {
            $priceCents = Arr::get($row, 'price_cents');
            if ($priceCents !== null) {
                $price = $this->normalizePrice(((float) $priceCents) / 100);
            }
        }

        $finalUrl = null;

        if ($this->isAbsoluteUrl($publicUrl)) {
            $finalUrl = $publicUrl;
        } elseif ($this->isAbsoluteUrl($image)) {
            $finalUrl = $image;
        }
        if (!$finalUrl && $publicUrl) {
            $finalUrl = $this->publicStorageUrl($supabaseUrl, $bucket, $publicUrl);
        }
        if (!$finalUrl && $imagePath) {
            $finalUrl = $this->publicStorageUrl($supabaseUrl, $bucket, $imagePath);
        }

        if (!$finalUrl) return null;

        return [
            'id'         => $id,
            'name'       => $name,
            'image_url'  => $finalUrl,
            'price'      => $price,
            'created_at' => $createdAt,
        ];
    }

    protected function isAbsoluteUrl(?string $val): bool
    {
        if (!$val) return false;
        return Str::startsWith($val, ['http://', 'https://']);
    }

    protected function publicStorageUrl(string $supabaseUrl, string $bucket, string $path): string
    {
        $path = ltrim($path, '/');
        return rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucket . '/' . $path;
    }

    protected function fallbackLocal(int $limit): array
    {
        $rows = Product::query()
            ->select(['id', 'name', 'image', 'image_url', 'price'])
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($rows as $p) {
            $candidatos = [
                $p->image_url ?? null,
                $p->image ?? null,
            ];
            $url = collect($candidatos)->first(fn ($u) => is_string($u) && Str::startsWith($u, ['http://', 'https://']));
            if ($url) {
                $out[] = [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'image_url' => $url,
                    'price'     => $this->normalizePrice($p->price),
                ];
            }
        }
        return $out;
    }

    protected function normalizePrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $normalized = str_replace(['$', ',', ' '], ['', '', ''], $value);
        } else {
            $normalized = $value;
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        $number = (float) $normalized;
        return $number >= 0 ? $number : null;
    }
}
