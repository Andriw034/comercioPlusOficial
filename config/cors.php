<?php

$parseCsv = static function (string $value): array {
    return array_values(array_filter(array_map(
        static fn (string $entry) => trim($entry),
        explode(',', $value)
    )));
};

$configuredOrigins = array_values(array_filter(array_map(
    static fn (string $origin) => trim($origin),
    $parseCsv((string) env('CORS_ALLOWED_ORIGINS', ''))
)));

$configuredOriginPatterns = array_values(array_filter(array_map(
    static fn (string $pattern) => trim($pattern),
    $parseCsv((string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))
)));

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

$frontendOriginsFromEnv = array_values(array_filter([
    trim((string) env('FRONTEND_URL', '')),
]));

$defaultOrigins = array_values(array_unique(array_merge(
    $localDevOrigins,
    $projectVercelOrigins,
    $frontendOriginsFromEnv
)));

$allowedOrigins = array_values(array_unique(array_merge(
    $defaultOrigins,
    $configuredOrigins
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configura los permisos para que tu frontend pueda hacer peticiones al backend.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $configuredOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
