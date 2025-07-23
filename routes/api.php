<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderMessageController;
use App\Http\Controllers\Api\OrderProductController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PruebaController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TutorialController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API
|--------------------------------------------------------------------------
|
| Aquí puedes registrar las rutas API para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider y todas ellas
| serán asignadas al grupo de middleware "api".
|
*/

// Ruta para verificar que la API está activa
Route::get('/ping', function () {
    return response()->json(['message' => 'API OK'], 200);
});

// Ruta para obtener el usuario autenticado (requiere autenticación)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de recursos para usuarios
Route::resource('users', UserController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->names([
        'index'   => 'api.v1.users.index',
        'show'    => 'api.v1.users.show',
        'store'   => 'api.v1.users.store',
        'update'  => 'api.v1.users.update',
        'destroy' => 'api.v1.users.destroy',
    ]);

// Rutas de recursos para productos
Route::resource('products', ProductController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->names([
        'index'   => 'api.v1.products.index',
        'show'    => 'api.v1.products.show',
        'store'   => 'api.v1.products.store',
        'update'  => 'api.v1.products.update',
        'destroy' => 'api.v1.products.destroy',
    ]);

/*
|---------------------------------------------------------------------------
| Rutas de recursos para otros modelos
|---------------------------------------------------------------------------
| Ejemplo para hacer consultas en Postman con Cart:
| - Para incluir relaciones: ?include=relation1,relation2
| - Para filtrar: ?filter[field]=value
| - Para ordenar: ?sort=field (ascendente) o ?sort=-field (descendente)
| - Para paginar: ?page=1&per_page=10
| Ejemplo completo:
| GET /api/carts?include=user&filter[status]=active&sort=-created_at&page=1&per_page=10
*/
Route::resource('carts', CartController::class);
Route::resource('cart-products', CartProductController::class);
Route::resource('categories', CategoryController::class);
Route::resource('channels', ChannelController::class);
Route::resource('claims', ClaimController::class);
Route::resource('locations', LocationController::class);
Route::resource('notifications', NotificacionController::class);
Route::resource('orders', OrderController::class);
Route::resource('order-messages', OrderMessageController::class);
Route::resource('order-products', OrderProductController::class);
Route::resource('profiles', ProfileController::class);
Route::resource('pruebas', PruebaController::class);
Route::resource('ratings', RatingController::class);
Route::resource('roles', RoleController::class);
Route::resource('sales', SaleController::class);
Route::resource('settings', SettingController::class);
Route::resource('tutorials', TutorialController::class);
