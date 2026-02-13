<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\CartProductController;
use App\Http\Controllers\Api\HeroImageController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    $dbOk = false;
    $dbError = null;
    $checks = [
        'users_table' => null,
        'products_table' => null,
        'categories_table' => null,
        'personal_access_tokens_table' => null,
        'users_role_column' => null,
        'categories_slug_column' => null,
    ];

    try {
        DB::connection()->getPdo();
        DB::select('select 1');
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }

    if ($dbOk) {
        $checks = [
            'users_table' => Schema::hasTable('users'),
            'products_table' => Schema::hasTable('products'),
            'categories_table' => Schema::hasTable('categories'),
            'personal_access_tokens_table' => Schema::hasTable('personal_access_tokens'),
            'users_role_column' => Schema::hasColumn('users', 'role'),
            'categories_slug_column' => Schema::hasColumn('categories', 'slug'),
        ];
    }

    $status = $dbOk ? 'ok' : 'degraded';
    $defaultConnection = config('database.default');
    $connectionConfig = (array) config("database.connections.{$defaultConnection}", []);

    return response()->json([
        'status' => $status,
        'db_ok' => $dbOk,
        'checks' => $checks,
        'db_error' => $dbError,
        'db_connection' => [
            'default' => $defaultConnection,
            'driver' => $connectionConfig['driver'] ?? null,
            'host' => $connectionConfig['host'] ?? null,
            'port' => $connectionConfig['port'] ?? null,
            'database' => $connectionConfig['database'] ?? null,
            'username_set' => !empty($connectionConfig['username']),
            'password_set' => isset($connectionConfig['password']) && $connectionConfig['password'] !== '',
            'url_set' => !empty($connectionConfig['url']),
        ],
        'env_hints' => [
            'has_db_connection' => (bool) env('DB_CONNECTION'),
            'has_db_host' => (bool) env('DB_HOST'),
            'has_mysqlhost' => (bool) env('MYSQLHOST'),
            'has_pg_host' => (bool) env('PGHOST'),
            'has_database_url' => (bool) env('DATABASE_URL'),
        ],
        'timestamp' => now()->toIso8601String(),
    ], $dbOk ? 200 : 503);
});

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Public resources (read-only)
|--------------------------------------------------------------------------
*/

Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

Route::get('public-stores', [StoreController::class, 'publicStores']);
Route::get('public-stores/{store}', [StoreController::class, 'show']);
Route::get('hero-images', [HeroImageController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected API (auth:sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role'  => $user->roleKey(),
            'has_store' => $user->hasStore(),
            'store_id' => optional($user->store)->id,
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('cart', CartController::class);
    Route::apiResource('cart-products', CartProductController::class);
    // Client orders
    Route::middleware('role.key:client')->group(function () {
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    });

    // Merchant orders
    Route::middleware('role.key:merchant')->group(function () {
        Route::get('merchant/orders', [OrderController::class, 'merchantIndex']);
        Route::put('merchant/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('merchant/customers', [CustomerController::class, 'myCustomers']);
        Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    });
    Route::post('stores/{store}/visit', [CustomerController::class, 'registerVisit']);
    Route::apiResource('subscriptions', SubscriptionController::class);

    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('stores', StoreController::class)->except(['index', 'show']);
    Route::get('my/store', [StoreController::class, 'myStore'])->middleware('role.key:merchant');

    Route::prefix('uploads')->group(function () {
        Route::post('products', [UploadController::class, 'storeProductImage']);
        Route::post('stores/logo', [UploadController::class, 'storeStoreLogo']);
        Route::post('stores/cover', [UploadController::class, 'storeStoreCover']);
        Route::post('profiles/photo', [UploadController::class, 'storeProfilePhoto']);
    });
});
