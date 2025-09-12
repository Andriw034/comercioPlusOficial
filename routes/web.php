<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// Público (web)
use App\Http\Controllers\WebController;
use App\Http\Controllers\Web\StoreWebController as PublicStoreWebController;
use App\Http\Controllers\Web\ProductController as PublicProductController;
use App\Http\Controllers\Web\CategoryController as PublicCategoryController;

// Panel/Admin (interno)
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;     // crear/guardar tienda (interno)
use App\Http\Controllers\ProductController;  // gestión productos (interno)
use App\Http\Controllers\CategoryController; // gestión categorías (interno)
use App\Http\Controllers\OrmController;

/* HOME */
Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/welcome', fn () => view('welcome'))->name('welcome');

/* PRUEBAS */
Route::get('consulta', [OrmController::class, 'consulta']);
Route::view('/tailwind-test', 'tailwind-test')->name('tailwind.test');
Route::view('/vue-test', 'vue-test')->name('vue.test');

/* DASHBOARD genérico (si usas Inertia para otra cosa) */
Route::get('/dashboard', [WebController::class, 'dashboard'])
    ->middleware(['auth', HandleInertiaRequests::class])
    ->name('dashboard');

/* AUTENTICACIÓN */
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/* ÁREA AUTENTICADA */
Route::middleware('auth')->group(function () {
    // Perfil / compras
    Route::get('/cart', [\App\Http\Controllers\Web\CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [\App\Http\Controllers\Web\OrderController::class, 'create'])->name('checkout');
    Route::get('/orders', [\App\Http\Controllers\Web\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Web\OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Crear tienda
    Route::get('/stores/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/stores',        [StoreController::class, 'store'])->name('store.create.post');

    // PANEL ADMIN (sin has.store por ahora)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/update-image', [ProductController::class, 'updateImage'])->name('products.update-image');
        Route::resource('categories', CategoryController::class);
        // UI de productos (frontend demo con localStorage)
        Route::view('/products-ui', 'dashboard.products')->name('products.ui');
    });
});

/* PÚBLICO (Catálogo / Tiendas / Categorías) */
Route::middleware(HandleInertiaRequests::class)->group(function () {
    Route::get('/stores', [PublicStoreWebController::class, 'index'])->name('stores.index');

    // Evitar capturar /stores/create como slug:
    Route::get('/stores/{store}', [PublicStoreWebController::class, 'show'])
        ->where('store', '^(?!create$)[A-Za-z0-9\-_.]+$')
        ->name('stores.show');
});

Route::get('/products', [PublicProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [PublicProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [PublicCategoryController::class, 'show'])->name('categories.show');

/* EDUCATION ROUTES */
Route::prefix('yeargroups')->group(function () {
    Route::prefix('EYFS')->group(function () {
        Route::prefix('y0')->group(function () {
            Route::prefix('subjects')->group(function () {
                Route::get('phonics/index', fn () => view('yeargroups.EYFS.y0.subjects.phonics.index'))->name('yeargroups.eyfs.y0.subjects.phonics.index');
                Route::get('understanding-the-world/index', fn () => view('yeargroups.EYFS.y0.subjects.understanding-the-world.index'))->name('yeargroups.eyfs.y0.subjects.understanding-the-world.index');
                Route::prefix('maths')->group(function () {
                    Route::prefix('autumn')->group(function () {
                        Route::get('index', fn () => view('yeargroups.EYFS.y0.subjects.maths.autumn.index'))->name('yeargroups.eyfs.y0.subjects.maths.autumn.index');
                        Route::get('week1', fn () => view('yeargroups.EYFS.y0.subjects.maths.autumn.week1'))->name('yeargroups.eyfs.y0.subjects.maths.autumn.week1');
                        Route::get('week2', fn () => view('yeargroups.EYFS.y0.subjects.maths.autumn.week2'))->name('yeargroups.eyfs.y0.subjects.maths.autumn.week2');
                    });
                });
            });
        });
    });
});

Route::get('/lesson/counting-to-3', fn () => view('lesson.counting-to-3'))->name('lesson.counting-to-3');

/* USERS (si administras usuarios globalmente) */
Route::resource('users', UserController::class);

/* SPA opcional */
Route::view('/app/{any}', 'app')->where('any', '.*');

/* 404 */
Route::fallback(fn() => response()->view('errors.404', [], 404));
