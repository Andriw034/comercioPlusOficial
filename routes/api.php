<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Correctly namespacing the API controllers
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\CartProductController;

// Rutas Públicas
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Public endpoints for listing and showing resources
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('public-stores', StoreController::class)->only(['index', 'show']);

// Rutas Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    // Resource routes that require authentication
    Route::apiResource('users', UserController::class);
    Route::apiResource('cart', CartController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::apiResource('cart-products', CartProductController::class);

    // Write operations for resources that have public listings
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('stores', StoreController::class);
});
