<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Public resources (read-only)
|--------------------------------------------------------------------------
*/

Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

Route::get('stores', [StoreController::class, 'publicStores']);
Route::get('stores/{store}', [StoreController::class, 'show']);
Route::get('public-stores', [StoreController::class, 'publicStores']);
Route::get('public-stores/{id}', [StoreController::class, 'show']);
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
            'role'  => $user->role,
            'has_store' => $user->hasStore(),
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
    });
    Route::apiResource('subscriptions', SubscriptionController::class);

    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('stores', StoreController::class)->except(['index', 'show']);
    Route::get('my/store', [StoreController::class, 'myStore'])->middleware('role.key:merchant');
});
