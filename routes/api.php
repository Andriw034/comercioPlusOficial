<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PublicProductController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicStoreController;
use App\Http\Controllers\Api\ExternalProductController;
use App\Http\Controllers\Api\DemoImageController;
// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

// Ruta para verificar que la API está activa
Route::get('/ping', function () {
    return response()->json(['message' => 'API OK'], 200);
});

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

Route::get('/health', function () {
    try {
        // Verificar conexión a base de datos
        DB::connection()->getPdo();

        // Verificar que las tablas principales existen
        $tables = ['users', 'stores', 'products', 'categories'];
        $missingTables = [];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        $status = empty($missingTables) ? 'healthy' : 'degraded';
        $statusCode = empty($missingTables) ? 200 : 207; // 207 Multi-Status

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'services' => [
                'database' => [
                    'status' => 'connected',
                    'connection' => config('database.default')
                ],
                'cache' => [
                    'status' => Cache::store()->getStore() ? 'available' : 'unavailable'
                ]
            ],
            'metrics' => [
                'users_count' => \App\Models\User::count(),
                'stores_count' => \App\Models\Store::count(),
                'products_count' => \App\Models\Product::count(),
                'categories_count' => \App\Models\Category::count(),
            ],
            'warnings' => empty($missingTables) ? null : ['missing_tables' => $missingTables]
        ], $statusCode);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'timestamp' => now()->toISOString(),
            'error' => $e->getMessage(),
            'services' => [
                'database' => ['status' => 'disconnected'],
                'cache' => ['status' => 'unknown']
            ]
        ], 503);
    }
});

// Rutas públicas (sin autenticación requerida)
Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
Route::apiResource('public-stores', \App\Http\Controllers\Api\PublicStoreController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
Route::apiResource('subscriptions', \App\Http\Controllers\Api\SubscriptionController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Ruta para obtener el usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('users', UserController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store', 'update'])->names('api.orders');
    Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    // Cart: los tests piden /api/cart
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::get('cart/{cart}', [CartController::class, 'show']);
    Route::put('cart/{cart}', [CartController::class, 'update']);
    Route::delete('cart/{cart}', [CartController::class, 'destroy']);

    // Estadísticas
    Route::prefix('stats')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\Api\StatsController::class, 'summary']);
        Route::get('/timeseries', [\App\Http\Controllers\Api\StatsController::class, 'timeseries']);
        Route::get('/top-products', [\App\Http\Controllers\Api\StatsController::class, 'topProducts']);
    });
});

// Rutas públicas (sin autenticación) - estas deben ir DESPUÉS de las protegidas para tener prioridad
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/categories', [PublicCategoryController::class, 'index']);
Route::get('/public-stores', [PublicStoreController::class, 'index']);


Route::get('/ext/products', [ExternalProductController::class, 'index']);
Route::get('/ext/products/{externalId}', [ExternalProductController::class, 'show']);
Route::post('/ext/products', [ExternalProductController::class, 'store']);
Route::put('/ext/products/{externalId}', [ExternalProductController::class, 'update']);
Route::delete('/ext/products/{externalId}', [ExternalProductController::class, 'destroy']);

Route::get('/demo/images', [\App\Http\Controllers\Api\DemoImageController::class, 'index']);
