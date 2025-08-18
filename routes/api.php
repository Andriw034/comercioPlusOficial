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
use App\Http\Controllers\Api\PublicStoreController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TutorialController;
use App\Http\Controllers\Api\UserController;
use App\Models\PublicStore;
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
Route::resource('carts', CartController::class)->names([
    'index'   => 'api.v1.carts.index',
    'store'   => 'api.v1.carts.store',
    'show'    => 'api.v1.carts.show',
    'update'  => 'api.v1.carts.update',
    'destroy' => 'api.v1.carts.destroy',
]);

Route::resource('cart-products', CartProductController::class)->names([
    'index'   => 'api.v1.cart-products.index',
    'store'   => 'api.v1.cart-products.store',
    'show'    => 'api.v1.cart-products.show',
    'update'  => 'api.v1.cart-products.update',
    'destroy' => 'api.v1.cart-products.destroy',
]);

Route::resource('categories', CategoryController::class)->names([
    'index'   => 'api.v1.categories.index',
    'store'   => 'api.v1.categories.store',
    'show'    => 'api.v1.categories.show',
    'update'  => 'api.v1.categories.update',
    'destroy' => 'api.v1.categories.destroy',
]);

Route::resource('channels', ChannelController::class)->names([
    'index'   => 'api.v1.channels.index',
    'store'   => 'api.v1.channels.store',
    'show'    => 'api.v1.channels.show',
    'update'  => 'api.v1.channels.update',
    'destroy' => 'api.v1.channels.destroy',
]);

Route::resource('claims', ClaimController::class)->names([
    'index'   => 'api.v1.claims.index',
    'store'   => 'api.v1.claims.store',
    'show'    => 'api.v1.claims.show',
    'update'  => 'api.v1.claims.update',
    'destroy' => 'api.v1.claims.destroy',
]);

Route::resource('locations', LocationController::class)->names([
    'index'   => 'api.v1.locations.index',
    'store'   => 'api.v1.locations.store',
    'show'    => 'api.v1.locations.show',
    'update'  => 'api.v1.locations.update',
    'destroy' => 'api.v1.locations.destroy',
]);

Route::resource('notifications', NotificacionController::class)->names([
    'index'   => 'api.v1.notifications.index',
    'store'   => 'api.v1.notifications.store',
    'show'    => 'api.v1.notifications.show',
    'update'  => 'api.v1.notifications.update',
    'destroy' => 'api.v1.notifications.destroy',
]);

Route::resource('orders', OrderController::class)->names([
    'index'   => 'api.v1.orders.index',
    'store'   => 'api.v1.orders.store',
    'show'    => 'api.v1.orders.show',
    'update'  => 'api.v1.orders.update',
    'destroy' => 'api.v1.orders.destroy',
]);

Route::resource('order-messages', OrderMessageController::class)->names([
    'index'   => 'api.v1.order-messages.index',
    'store'   => 'api.v1.order-messages.store',
    'show'    => 'api.v1.order-messages.show',
    'update'  => 'api.v1.order-messages.update',
    'destroy' => 'api.v1.order-messages.destroy',
]);

Route::resource('order-products', OrderProductController::class)->names([
    'index'   => 'api.v1.order-products.index',
    'store'   => 'api.v1.order-products.store',
    'show'    => 'api.v1.order-products.show',
    'update'  => 'api.v1.order-products.update',
    'destroy' => 'api.v1.order-products.destroy',
]);

Route::resource('profiles', ProfileController::class)->names([
    'index'   => 'api.v1.profiles.index',
    'store'   => 'api.v1.profiles.store',
    'show'    => 'api.v1.profiles.show',
    'update'  => 'api.v1.profiles.update',
    'destroy' => 'api.v1.profiles.destroy',
]);

Route::resource('pruebas', PruebaController::class)->names([
    'index'   => 'api.v1.pruebas.index',
    'store'   => 'api.v1.pruebas.store',
    'show'    => 'api.v1.pruebas.show',
    'update'  => 'api.v1.pruebas.update',
    'destroy' => 'api.v1.pruebas.destroy',
]);

Route::resource('ratings', RatingController::class)->names([
    'index'   => 'api.v1.ratings.index',
    'store'   => 'api.v1.ratings.store',
    'show'    => 'api.v1.ratings.show',
    'update'  => 'api.v1.ratings.update',
    'destroy' => 'api.v1.ratings.destroy',
]);

Route::resource('roles', RoleController::class)->names([
    'index'   => 'api.v1.roles.index',
    'store'   => 'api.v1.roles.store',
    'show'    => 'api.v1.roles.show',
    'update'  => 'api.v1.roles.update',
    'destroy' => 'api.v1.roles.destroy',
]);

Route::resource('sales', SaleController::class)->names([
    'index'   => 'api.v1.sales.index',
    'store'   => 'api.v1.sales.store',
    'show'    => 'api.v1.sales.show',
    'update'  => 'api.v1.sales.update',
    'destroy' => 'api.v1.sales.destroy',
]);

Route::resource('settings', SettingController::class)->names([
    'index'   => 'api.v1.settings.index',
    'store'   => 'api.v1.settings.store',
    'show'    => 'api.v1.settings.show',
    'update'  => 'api.v1.settings.update',
    'destroy' => 'api.v1.settings.destroy',
]);

Route::resource('tutorials', TutorialController::class)->names([
    'index'   => 'api.v1.tutorials.index',
    'store'   => 'api.v1.tutorials.store',
    'show'    => 'api.v1.tutorials.show',
    'update'  => 'api.v1.tutorials.update',
    'destroy' => 'api.v1.tutorials.destroy',
]);
Route::resource('stores', \App\Http\Controllers\Api\StoreController::class)->names([
    'index'   => 'api.v1.stores.index',
    'store'   => 'api.v1.stores.store',
    'show'    => 'api.v1.stores.show',
    'update'  => 'api.v1.stores.update',
    'destroy' => 'api.v1.stores.destroy',
]);
Route::resource('publicstores', PublicStoreController::class)->names([
    'index'   => 'api.v1.publicstores.index',
    'store'   => 'api.v1.publicstores.store',
    'show'    => 'api.v1.publicstores.show',
    'update'  => 'api.v1.publicstores.update',
    'destroy' => 'api.v1.publicstores.destroy',
]);
