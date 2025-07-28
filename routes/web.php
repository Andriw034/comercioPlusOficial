<?php

use Illuminate\Support\Facades\Route;

// Controladores de autenticación
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Controladores generales
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\PublicStoreController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OrmController;

// -------------------------------------
// Página principal
// -------------------------------------
Route::get('/', function () {
    return view('welcome');
})->name('home');

// -------------------------------------
// Autenticación
// -------------------------------------
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// -------------------------------------
// Recuperación de contraseña
// -------------------------------------
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// -------------------------------------
// Panel de administrador
// -------------------------------------
Route::get('/admin', [DashboardController::class, 'index'])->middleware('auth')->name('admin.dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// -------------------------------------
// Zona privada (requiere login)
// -------------------------------------
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Productos
    Route::resource('products', ProductController::class);
    Route::get('/productos/crear', [ProductController::class, 'create'])->name('producto.create');
    Route::post('/producto/crear', [ProductController::class, 'store'])->name('producto.store');

    // Categorías
    Route::resource('categories', CategoryController::class);

    // Tiendas (Gestión del comerciante)
    Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store/create', [StoreController::class, 'store'])->name('store.create.post');
    Route::get('/merchant/store/edit', [StoreController::class, 'edit'])->name('merchant.store.edit');
    Route::put('/merchant/store/update', [StoreController::class, 'update'])->name('merchant.store.update');

    // Editar vista pública de tienda
    Route::get('/mi-tienda/editar', [PublicStoreController::class, 'edit'])->name('store.edit');
    Route::put('/mi-tienda/{store}', [PublicStoreController::class, 'update'])->name('store.update');

    // Configuración
    Route::get('/configuracion', [SettingController::class, 'showForm'])->name('settings.form');
    Route::post('/configuracion', [SettingController::class, 'saveSettings'])->name('settings.update');
});

// -------------------------------------
// Usuarios
// -------------------------------------
Route::resource('users', UserController::class);

// -------------------------------------
// Tienda pública (sin login)
// -------------------------------------
Route::get('/tienda/{slug}', [PublicStoreController::class, 'show'])->name('store.public');

// -------------------------------------
// Pruebas y consultas
// -------------------------------------
Route::get('/consulta', [OrmController::class, 'consulta']);

// -------------------------------------
// Otras vistas
// -------------------------------------
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
