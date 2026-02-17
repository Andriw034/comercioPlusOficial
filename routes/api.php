<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicProductController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WompiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - ComercioPlus (DIA 1)
|--------------------------------------------------------------------------
*/

// Health check for smoke tests.
Route::get('/health', fn () => response()->json(['status' => 'ok']));

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public aliases used by frontend and tests.
Route::get('/public-stores', [StoreController::class, 'publicStores']);
Route::get('/public-stores/{store}', [StoreController::class, 'show']);
Route::get('/public/products', [PublicProductController::class, 'index']);
Route::get('/public/categories', [PublicCategoryController::class, 'index']);
Route::get('/public/stores', [StoreController::class, 'publicStores']);
Route::get('/public/stores/{store}', [StoreController::class, 'show']);

// Public catalog used by frontend.
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Checkout order creation route used by Wompi flow.
Route::post('/orders/create', [WompiController::class, 'createOrder']);

// Wompi payment routes.
Route::prefix('payments/wompi')->group(function () {
    Route::post('/create', [WompiController::class, 'createPayment']);
    Route::post('/webhook', [WompiController::class, 'webhook']);
    Route::get('/status/{transactionId}', [WompiController::class, 'getTransactionStatus']);
    Route::get('/pse-banks', [WompiController::class, 'getPseBanks']);
});

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Stores.
    Route::get('/my/store', [StoreController::class, 'myStore']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    Route::delete('/stores/{store}', [StoreController::class, 'destroy']);

    // Customer-store interactions.
    Route::post('/stores/register-customer', [CustomerController::class, 'registerCustomer']);
    Route::post('/stores/{store}/visit', [CustomerController::class, 'registerVisit']);
    Route::post('/stores/{store}/follow', fn () => response()->json(['status' => 'ok']));
    Route::delete('/stores/{store}/follow', fn () => response()->json(['status' => 'ok']));

    // Merchant dashboard.
    Route::get('/merchant/orders', [OrderController::class, 'merchantIndex']);
    Route::put('/merchant/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/merchant/customers', [CustomerController::class, 'myCustomers']);
    Route::get('/merchant/stats', [StatsController::class, 'summary']);

    // Product/category management.
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // API resources used by current tests.
    Route::apiResource('users', UserController::class);
    Route::apiResource('cart', CartController::class);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::apiResource('cart-products', CartProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);
});
