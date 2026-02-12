<?php

$parseCsv = static function (string $value): array {
    return array_values(array_filter(array_map(
        static fn (string $entry) => trim($entry),
        explode(',', $value)
    )));
};

$vercelProdOrigin = trim((string) env('VERCEL_PROD_ORIGIN', ''));
$frontendOrigin = trim((string) env('FRONTEND_URL', ''));

$allowedOrigins = array_values(array_unique(array_merge(
    [
        'https://comercio-plus-oficial.vercel.app',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],
    $vercelProdOrigin !== '' ? [$vercelProdOrigin] : [],
    $frontendOrigin !== '' ? [$frontendOrigin] : [],
    $parseCsv((string) env('CORS_ALLOWED_ORIGINS', ''))
)));

$allowedOriginPatterns = array_values(array_unique(array_merge(
    [
        '#^https://[a-z0-9-]+\.vercel\.app$#i',
    ],
    $parseCsv((string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
