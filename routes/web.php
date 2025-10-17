<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;

// Auth controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// Web controllers (organizados)
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\StoreController;
use App\Http\Controllers\Web\ProductController;                // Admin (dashboard)
use App\Http\Controllers\Web\CategoryController as AdminCategoryController; // Admin (dashboard)
use App\Http\Controllers\Web\StoreWebController as PublicStoreWebController; // Público (listado/tienda)
use App\Http\Controllers\Web\ProductController as PublicProductController;   // Público (productos)
use App\Http\Controllers\Web\CategoryController as PublicCategoryController; // Público (categorías, si aplica)

// Otros controladores
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrmController;
use App\Http\Controllers\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\DashboardProductsController;
use App\Http\Controllers\AdminController;

// API-like (web auth) - Ejemplo de AJAX categorías
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;

/*
|--------------------------------------------------------------------------
| Básico / Público
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
| Dashboard principal (simple)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Área autenticada (usuario)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Ecommerce / profile
    Route::get('/cart', [\App\Http\Controllers\Web\CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [\App\Http\Controllers\Web\OrderController::class, 'create'])->name('checkout');
    Route::get('/orders', [\App\Http\Controllers\Web\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Web\OrderController::class, 'show'])->name('orders.show');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Settings (Mi perfil)
    Route::get('/settings/profile', [SettingsProfileController::class, 'edit'])->name('settings.profile');
    Route::put('/settings/profile', [SettingsProfileController::class, 'update'])->name('settings.profile.update');

    /*
    |--------------------------------------------------------------------------
    | Crear tienda / tienda (usuario)
    |--------------------------------------------------------------------------
    */
    Route::get('/crear-tienda', fn() => view('store_wizard'))->name('store.wizard');

    Route::get('/tienda/crear', [StoreController::class, 'create'])->name('store.create');
    Route::post('/tienda',        [StoreController::class, 'store'])->name('store.store');

    // Alias de compatibilidad
    if (Route::has('store.create')) {
        Route::get('/tienda/crear-compat', fn() => redirect()->route('store.create'))->name('stores.create');
    } elseif (Route::has('store.wizard')) {
        Route::get('/crear-tienda-compat', fn() => redirect()->route('store.wizard'))->name('stores.create');
    } else {
        Route::get('/crear-tienda-compat', fn() => redirect()->route('dashboard'))->name('stores.create');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin / Panel (requiere has.store middleware)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('has.store')->group(function () {
        // Dashboard admin
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Productos (admin.products.*)
        Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
        Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::patch('/productos/{product}/update-image', [ProductController::class, 'updateImage'])->name('products.update-image');


        
        // Categorías (solo lo implementado: index/create/store)
        Route::resource('categories', AdminCategoryController::class)->only(['index','create','store']);

        // UI demo
        Route::view('/products-ui', 'dashboard.products')->name('products.ui');

        // Store settings
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('index');
            Route::get('/appearance', [StoreController::class, 'appearance'])->name('appearance');
            Route::post('/appearance', [StoreController::class, 'updateAppearance'])->name('appearance.update');
            Route::get('/payments',   [StoreController::class, 'payments'])->name('payments');
            Route::get('/shipping',   [StoreController::class, 'shipping'])->name('shipping');
            Route::get('/domain',     [StoreController::class, 'domain'])->name('domain');
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
    |--------------------------------------------------------------------------
    | API-like AJAX (web auth)
    |--------------------------------------------------------------------------
    */
    Route::post('/api/categories', [ApiCategoryController::class, 'store'])
        ->name('api.categories.store');
});

/*
|--------------------------------------------------------------------------
| Público (Tiendas / Productos / Categorías)
|--------------------------------------------------------------------------
*/
Route::middleware(HandleInertiaRequests::class)->group(function () {
    Route::get('/stores', [PublicStoreWebController::class, 'index'])->name('stores.index');

    // Evita capturar /stores/create como slug
    Route::get('/stores/{store}', [PublicStoreWebController::class, 'show'])
        ->where('store', '^(?!create$)[A-Za-z0-9\-_.]+$')
        ->name('stores.show');
});

Route::get('/products', [PublicProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [PublicProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [PublicCategoryController::class, 'show'])->name('categories.show');

/*
|--------------------------------------------------------------------------
| Education
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
Route::resource('users', UserController::class);

/*
|--------------------------------------------------------------------------
| SPA opcional
|--------------------------------------------------------------------------
*/
Route::view('/app/{any}', 'app')->where('any', '.*');

/*
|--------------------------------------------------------------------------
| 404 fallback
|--------------------------------------------------------------------------
*/
Route::fallback(fn() => response()->view('errors.404', [], 404));



Route::get('/storagetest', function () {
    \Illuminate\Support\Facades\Storage::disk('public')
        ->put('health.txt', 'ok ' . now());
    return asset('storage/health.txt');
});
