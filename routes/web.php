<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;

// Auth controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// Public web controllers
use App\Http\Controllers\WebController;
use App\Http\Controllers\Web\StoreWebController as PublicStoreWebController;
use App\Http\Controllers\Web\ProductController as PublicProductController;
use App\Http\Controllers\Web\CategoryController as PublicCategoryController;

// Dashboard / admin controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;      // crear/guardar tienda (interno)
use App\Http\Controllers\ProductController;   // gestión productos (interno)
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;  // gestión categorías (interno)
use App\Http\Controllers\OrmController;

// Settings
use App\Http\Controllers\Settings\ProfileController as SettingsProfileController;

use App\Http\Controllers\EducationController;
use App\Http\Controllers\DashboardProductsController;
use App\Http\Controllers\AdminController;

// API (AJAX) controllers (web-authenticated)
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;

/*
|--------------------------------------------------------------------------
| Basic / Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/welcome', fn() => view('welcome'))->name('welcome');

/*
|--------------------------------------------------------------------------
| Demos / Helpers
|--------------------------------------------------------------------------
*/
Route::get('consulta', [OrmController::class, 'consulta']);
Route::view('/tailwind-test', 'tailwind-test')->name('tailwind.test');
Route::view('/vue-test', 'vue-test')->name('vue.test');

/*
|--------------------------------------------------------------------------
| Dashboard principal (ruta simple)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin routes (under auth and has.store middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'has.store'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard ya existe (no tocar)

    // Settings (pestañas)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index'); // Pestañas: general, appearance, payments, shipping, taxes, notifications
        Route::put('/general', [App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('update.general');
        Route::put('/appearance', [App\Http\Controllers\Admin\SettingsController::class, 'updateAppearance'])->name('update.appearance');
        Route::put('/payments', [App\Http\Controllers\Admin\SettingsController::class, 'updatePayments'])->name('update.payments');
        Route::put('/shipping', [App\Http\Controllers\Admin\SettingsController::class, 'updateShipping'])->name('update.shipping');
        Route::put('/taxes', [App\Http\Controllers\Admin\SettingsController::class, 'updateTaxes'])->name('update.taxes');
        Route::put('/notifications', [App\Http\Controllers\Admin\SettingsController::class, 'updateNotifications'])->name('update.notifications');
    });

    // Categorías
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->names('categories');
    Route::post('categories/store-json', [App\Http\Controllers\Admin\CategoryController::class, 'storeJson'])->name('categories.store_json');

    // Productos
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class)->names('products');
});

/*
|--------------------------------------------------------------------------
| Authentication (guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

/*
|--------------------------------------------------------------------------
| Authenticated area (user features)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Ecommerce / profile
    Route::get('/cart', [\App\Http\Controllers\Web\CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [\App\Http\Controllers\Web\OrderController::class, 'create'])->name('checkout');
    Route::get('/orders', [\App\Http\Controllers\Web\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Web\OrderController::class, 'show'])->name('orders.show');

    // Profile
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Settings (Mi perfil)
    Route::get('/settings/profile', [SettingsProfileController::class, 'edit'])->name('settings.profile');
    Route::put('/settings/profile', [SettingsProfileController::class, 'update'])->name('settings.profile.update');

    /*
    |----------------------------------------------------------------------
    | Crear tienda / tienda (usuario)
    |----------------------------------------------------------------------
    */
    Route::get('/crear-tienda', fn() => view('store_wizard'))->name('store.wizard');

    Route::get('/tienda/crear', [StoreController::class, 'create'])->name('store.create');
    Route::post('/tienda',        [StoreController::class, 'store'])->name('store.store');

    /*
    |----------------------------------------------------------------------
    | Alias de compatibilidad (para controladores viejos que apuntan a stores.create)
    |----------------------------------------------------------------------
    | Crea la ruta con nombre `stores.create` que redirige a la ruta real disponible.
    */
    if (Route::has('store.create')) {
        Route::get('/tienda/crear-compat', fn() => redirect()->route('store.create'))->name('stores.create');
    } elseif (Route::has('store.wizard')) {
        Route::get('/crear-tienda-compat', fn() => redirect()->route('store.wizard'))->name('stores.create');
    } else {
        Route::get('/crear-tienda-compat', fn() => redirect()->route('dashboard'))->name('stores.create');
    }

    /*
    |----------------------------------------------------------------------
    | Admin / Panel (requiere has.store middleware)
    | Rutas agrupadas con prefijo "admin" y nombre "admin."
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('has.store')->group(function () {
        // Dashboard admin
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Productos (admin.productos.*) - Rutas en español
        Route::get('/productos', [ProductController::class, 'index'])->name('productos.index');
        Route::get('/productos/crear', [ProductController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('productos.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{product}', [ProductController::class, 'update'])->name('productos.update');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('productos.destroy');
        Route::patch('/productos/{product}/update-image', [ProductController::class, 'updateImage'])->name('productos.update-image');

        // Categorías (resource admin.categories.*)
        Route::resource('categories', AdminCategoryController::class);

        // UI demo
        Route::view('/products-ui', 'dashboard.products')->name('products.ui');

        // Extras / Store settings
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('appearance', [StoreController::class, 'appearance'])->name('appearance');
            Route::put('appearance', [StoreController::class, 'updateAppearance'])->name('update_appearance');
            Route::get('payments',   [StoreController::class, 'payments'])->name('payments');
            Route::get('shipping',   [StoreController::class, 'shipping'])->name('shipping');
            Route::get('domain',     [StoreController::class, 'domain'])->name('domain');
        });

        // Perfil admin
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/security',      [UserController::class, 'security'])->name('security');
            Route::get('/notifications', [UserController::class, 'notifications'])->name('notifications');
        });

        // Users management (admin.users.*)
        Route::resource('users', AdminController::class)->names('users');
    });

    /*
    |----------------------------------------------------------------------
    | API-like AJAX endpoints (web auth) - e.g. crear categoría sin recargar
    |----------------------------------------------------------------------
    | Usamos rutas en web.php protegidas con auth para trabajar via session/AJAX.
    */
    Route::post('/api/categories', [ApiCategoryController::class, 'store'])
        ->name('api.categories.store')
        ->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| Storefront (Tienda propia del usuario autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('storefront')->name('storefront.')->group(function () {
    Route::get('/', [App\Http\Controllers\Web\StorefrontController::class, 'index'])->name('index');
    Route::get('/products/{product}', [App\Http\Controllers\Web\StorefrontController::class, 'show'])->name('products.show');
});

/*
|--------------------------------------------------------------------------
| Tienda pública por slug (sin auth)
|--------------------------------------------------------------------------
*/
Route::prefix('store')->name('storefront.public.')->group(function () {
    Route::get('{slug}', [App\Http\Controllers\Web\StorefrontController::class, 'publicIndex'])->name('home');
    Route::get('{slug}/p/{product:slug}', [App\Http\Controllers\Web\StorefrontController::class, 'publicShow'])->name('product.show');
});

// Atajo útil: /storefront redirige a la primera tienda (para pruebas locales)
Route::get('/storefront', function () {
    $store = \App\Models\Store::query()->firstOrFail();
    return redirect()->route('storefront.public.home', $store->slug);
})->name('storefront.shortcut');

/*
|--------------------------------------------------------------------------
| Público (Tiendas / Productos / Categorías)
|--------------------------------------------------------------------------
*/
Route::middleware(HandleInertiaRequests::class)->group(function () {
    Route::get('/stores', [PublicStoreWebController::class, 'index'])->name('stores.index');

    // Evitar capturar /stores/create como slug:
    Route::get('/stores/{store}', [PublicStoreWebController::class, 'show'])
        ->where('store', '^(?!create$)[A-Za-z0-9\-_.]+$')
        ->name('stores.show');
});

Route::get('/products', [PublicProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [PublicProductController::class, 'show'])->name('public.products.show');
Route::get('/categories/{category}', [PublicCategoryController::class, 'show'])->name('public.categories.show');

/*
|--------------------------------------------------------------------------
| Education routes (your custom)
|--------------------------------------------------------------------------
*/
Route::prefix('yeargroups')->group(function () {
    Route::prefix('EYFS')->group(function () {
        Route::prefix('y0')->group(function () {
            Route::prefix('subjects')->group(function () {
                Route::get('phonics/index', [EducationController::class, 'phonicsIndex'])->name('yeargroups.eyfs.y0.subjects.phonics.index');
                Route::get('understanding-the-world/index', [EducationController::class, 'understandingTheWorldIndex'])->name('yeargroups.eyfs.y0.subjects.understanding-the-world.index');

                Route::prefix('maths')->group(function () {
                    Route::prefix('autumn')->group(function () {
                        Route::get('index', [EducationController::class, 'mathsAutumnIndex'])->name('yeargroups.eyfs.y0.subjects.maths.autumn.index');
                        Route::get('week1', [EducationController::class, 'mathsAutumnWeek1'])->name('yeargroups.eyfs.y0.subjects.maths.autumn.week1');
                        Route::get('week2', [EducationController::class, 'mathsAutumnWeek2'])->name('yeargroups.eyfs.y0.subjects.maths.autumn.week2');
                    });
                });
            });
        });
    });
});

Route::get('/lesson/counting-to-3', [EducationController::class, 'lessonCountingTo3'])->name('lesson.counting-to-3');

/*
|--------------------------------------------------------------------------
| Users resource (general)
|--------------------------------------------------------------------------
*/
Route::resource('users', UserController::class)->names('public.users');

/*
|--------------------------------------------------------------------------
| SPA optional
|--------------------------------------------------------------------------
*/
Route::view('/app/{any}', 'app')->where('any', '.*');

/*
|--------------------------------------------------------------------------
| 404 fallback
|--------------------------------------------------------------------------
*/
Route::fallback(fn() => response()->view('errors.404', [], 404));
