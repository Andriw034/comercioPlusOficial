<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderProductController;
use App\Http\Controllers\Api\OrderMessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TutorialController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartProductController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PublicStoreController;
use App\Http\Controllers\Api\SubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'API is working']);
});

// Rutas públicas (sin autenticación)
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'apiRegister']);
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'apiLogin']);
Route::get('/public-stores', [PublicStoreController::class, 'index']);
Route::get('/public-stores/{store}', [PublicStoreController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
// Categorías públicas
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/public-stores', [PublicStoreController::class, 'index']);
Route::get('/public-stores/{id}', [PublicStoreController::class, 'show']);
Route::get('/tutorials', [TutorialController::class, 'index']);
Route::get('/tutorials/{tutorial}', [TutorialController::class, 'show']);
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{location}', [LocationController::class, 'show']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Usuarios
    Route::apiResource('users', UserController::class);

    // Productos
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Categorías (sin index/show públicos ya definidos arriba)
    Route::apiResource('categories', CategoryController::class)->except(['index','show']);

    // Órdenes
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-products', OrderProductController::class);
    Route::apiResource('order-messages', OrderMessageController::class);

    // Perfiles
    Route::apiResource('profiles', ProfileController::class);

    // Roles
    Route::apiResource('roles', RoleController::class);

    // Ventas
    Route::apiResource('sales', SaleController::class);

    // Configuraciones
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    Route::post('/settings', [SettingController::class, 'updateSettings']);

    // Tutoriales
    Route::post('/tutorials', [TutorialController::class, 'store']);
    Route::put('/tutorials/{tutorial}', [TutorialController::class, 'update']);
    Route::delete('/tutorials/{tutorial}', [TutorialController::class, 'destroy']);

    // Ubicaciones
    Route::post('/locations', [LocationController::class, 'store']);
    Route::put('/locations/{location}', [LocationController::class, 'update']);
    Route::delete('/locations/{location}', [LocationController::class, 'destroy']);

    // Canales
    Route::apiResource('channels', ChannelController::class);

    // Reclamos
    Route::apiResource('claims', ClaimController::class);

    // Carrito de compras
    Route::apiResource('cart', CartController::class);
    Route::apiResource('cart-products', CartProductController::class);

    // Calificaciones
    Route::apiResource('ratings', RatingController::class);

    // Notificaciones
    Route::apiResource('notifications', NotificacionController::class);

    // Tiendas públicas
    Route::post('/public-stores', [PublicStoreController::class, 'store']);
    Route::put('/public-stores/{store}', [PublicStoreController::class, 'update']);
    Route::delete('/public-stores/{store}', [PublicStoreController::class, 'destroy']);

    // Suscripciones
    Route::apiResource('subscriptions', SubscriptionController::class);
});
