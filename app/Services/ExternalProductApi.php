<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ExternalProductApi
{
    protected string $base;
    protected ?string $apiKey;

    public function __construct()
    {
        $cfg = config('services.products_external', []);
        $this->base   = rtrim((string) ($cfg['base_url'] ?? ''), '/');
        $this->apiKey = $cfg['api_key'] ?? null;
    }

    protected function client(): PendingRequest
    {
        $client = Http::timeout(15)
            ->retry(2, 500)
            ->acceptJson();

        if ($this->apiKey) {
            $client = $client->withToken($this->apiKey);
        }

        return $client;
    }

    public function list(array $filters = [], int $perPage = 15, int $page = 1): array
    {
        // DummyJSON: /products?limit=&skip=&q=
        $params = [];

        if (!empty($filters['search'])) $params['q'] = $filters['search'];
        if (!empty($filters['sort']))   $params['sortBy'] = explode(':', $filters['sort'])[0] ?? null;
        if (!empty($filters['sort']))   $params['order']  = (explode(':', $filters['sort'])[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $params['limit'] = $perPage;
        $params['skip']  = max(0, ($page - 1) * $perPage);

        $res = $this->client()->get("{$this->base}/products", $params);
        $res->throw();

        return $res->json(); // { products, total, skip, limit }
    }

    public function get(string $externalId): array
    {
        $res = $this->client()->get("{$this->base}/products/{$externalId}");
        $res->throw();
        return $res->json();
    }

    public function create(array $payload): array
    {
        // DummyJSON usa /products/add
        $mapped = [
            'title'       => Arr::get($payload, 'name'),
            'description' => Arr::get($payload, 'description'),
            'price'       => Arr::get($payload, 'price'),
            'stock'       => Arr::get($payload, 'stock'),
            'brand'       => Arr::get($payload, 'brand'),
            'category'    => Arr::get($payload, 'category_id'),
        ];

        $res = $this->client()->post("{$this->base}/products/add", $mapped);
        $res->throw();
        return $res->json();
    }

    public function update(string $externalId, array $payload): array
    {
        $mapped = array_filter([
            'title'       => Arr::get($payload, 'name'),
            'description' => Arr::get($payload, 'description'),
            'price'       => Arr::get($payload, 'price'),
            'stock'       => Arr::get($payload, 'stock'),
            'brand'       => Arr::get($payload, 'brand'),
            'category'    => Arr::get($payload, 'category_id'),
        ], fn($v) => !is_null($v));

        $res = $this->client()->put("{$this->base}/products/{$externalId}", $mapped);
        $res->throw();
        return $res->json();
    }

    public function delete(string $externalId): array
    {
        try {
            $res = $this->client()->delete("{$this->base}/products/{$externalId}");
            $res->throw();
            return $res->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return [
                'error' => true,
                'status' => $e->response ? $e->response->status() : 500,
                'message' => $e->response ? ($e->response->json('message') ?? 'Not found') : $e->getMessage(),
            ];
        }
    }
}
