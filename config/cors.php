<?php

$parseCsv = static function (string $value): array {
    return array_values(array_filter(array_map(
        static fn (string $entry) => trim($entry),
        explode(',', $value)
    )));
};

$localDevOrigins = [
    'http://127.0.0.1:3000',
    'http://localhost:3000',
    'http://127.0.0.1:3001',
    'http://localhost:3001',
    'http://127.0.0.1:5173',
    'http://localhost:5173',
];

$projectVercelOrigins = [
    'https://comercio-plus-oficial.vercel.app',
    'https://comercio-plus-oficial-*.vercel.app',
];

$frontendOrigin = trim((string) env('FRONTEND_URL', ''));

$allowedOrigins = array_values(array_unique(array_merge(
    $localDevOrigins,
    $projectVercelOrigins,
    $frontendOrigin !== '' ? [$frontendOrigin] : [],
    $parseCsv((string) env('CORS_ALLOWED_ORIGINS', ''))
)));

$allowedOriginPatterns = array_values(array_unique(array_merge(
    [
        '#^https://comercio-plus-oficial-[a-z0-9-]+\.vercel\.app$#',
    ],
    $parseCsv((string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
