<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PosthogService
{
    protected string $host;
    protected ?string $projectId;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->host = rtrim(config('services.posthog.host', env('POSTHOG_HOST', 'https://app.posthog.com')), '/');
        $this->projectId = (string) config('services.posthog.project_id', env('POSTHOG_PROJECT_ID'));
        $this->apiKey = (string) config('services.posthog.personal_api_key', env('POSTHOG_PERSONAL_API_KEY'));
    }

    public function query(string $hogql): array
    {
        if (!$this->projectId || !$this->apiKey) {
            return ['ok' => false, 'status' => 0, 'error' => 'PostHog credentials missing'];
        }

        $url = "{$this->host}/api/projects/{$this->projectId}/query";

        $res = Http::withHeaders([
                'Authorization'   => 'Bearer ' . $this->apiKey,
                'Content-Type'    => 'application/json',
                'POSTHOG-API-KEY' => $this->apiKey,
            ])
            ->acceptJson()
            ->post($url, [
                'query' => [
                    'kind'  => 'HogQLQuery',
                    'query' => $hogql,
                ],
            ]);

        if (!$res->ok()) {
            return ['ok' => false, 'status' => $res->status(), 'error' => $res->json()];
        }

        $json = $res->json();
        $columns = Arr::get($json, 'columns', []);
        $results = Arr::get($json, 'results', []);

        return ['ok' => true, 'columns' => $columns, 'rows' => $results];
    }
}
