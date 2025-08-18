<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\PublicStoreController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Página principal (Welcome)
|--------------------------------------------------------------------------
*/
Route::view('/', 'welcome')->name('welcome');

/*
|--------------------------------------------------------------------------
| Público (catálogo)
|--------------------------------------------------------------------------
*/
Route::get('/tienda/{slug}', [PublicStoreController::class, 'show'])->name('public.store.show');

/*
|--------------------------------------------------------------------------
| Autenticación (sin Spatie)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Recuperación de contraseña
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Zona privada (requiere login)
|--------------------------------------------------------------------------
*/
Route::get('/post-login', [LoginController::class, 'postLoginRedirect'])->name('post.login');

Route::middleware('auth')->group(function () {

    // Dashboard genérico
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin (puede usar el mismo controlador por ahora)
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::middleware('role:comerciante')->group(function () {
        // Atajo "Mi Tienda"
        Route::get('/store', function () {
            $user = auth()->user();
            if (! $user->store) {
                return redirect()->route('store.create');
            }
            return redirect()->route('store.edit', ['store' => $user->store->id]);
        })->name('store.index');

        // Tiendas
        Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
        Route::post('/store',        [StoreController::class, 'store'])->name('store.store');
        Route::get('/store/{store}/edit', [StoreController::class, 'edit'])->name('store.edit');
        Route::put('/store/{store}',       [StoreController::class, 'update'])->name('store.update');
        Route::delete('/store/{store}',    [StoreController::class, 'destroy'])->name('store.destroy');
        Route::get('/store/success', [StoreController::class, 'success'])->name('store.success');

        // Productos / Categorías
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->only(['index','store','update','destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Debug (solo local, opcional)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::get('/debug/roles', function () {
        if (!auth()->check()) return 'No autenticado';
        $user = auth()->user();

        return [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => $user->role,
        ];
    });
}

/*
|--------------------------------------------------------------------------
| Fallback 404
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
