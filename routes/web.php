<?php

use Illuminate\Support\Facades\Route;

// Auth Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Otros controladores
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StorePublicController;
use App\Http\Controllers\OrmController;

// -------------------------------------
// Página principal
// -------------------------------------
Route::get('/', function () {
    return view('welcome');
});

// -------------------------------------
// Autenticación
// -------------------------------------
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

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
Route::get('/admin', [DashboardController::class, 'index'])->middleware(['auth'])->name('admin.dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// -------------------------------------
// Usuarios
// -------------------------------------
Route::resource('users', UserController::class);

// -------------------------------------
// Zona privada (requiere login)
// -------------------------------------
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Productos
    Route::resource('products', ProductController::class);

    // Categorías
    Route::resource('categories', CategoryController::class);
});

// -------------------------------------
// Gestión de tienda por comerciantes
// -------------------------------------
Route::middleware(['auth', 'role:comerciante'])->group(function () {
    // Crear tienda
    Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store', [StoreController::class, 'store'])->name('store.store');

    // Editar tienda
    Route::get('/merchant/store/edit', [StoreController::class, 'edit'])->name('merchant.store.edit');
    Route::put('/merchant/store/update', [StoreController::class, 'update'])->name('merchant.store.update');
});

// -------------------------------------
// Vistas públicas de tiendas
// -------------------------------------

// Mostrar mini tienda pública con productos
Route::get('/tienda/{slug}', [StorePublicController::class, 'show'])->name('store.public');

// (Opcional) rutas públicas para editar desde vista pública (según permisos)
Route::middleware('auth')->group(function () {
    Route::get('/mi-tienda/editar', [StorePublicController::class, 'edit'])->name('store.edit');
    Route::put('/mi-tienda/{store}', [StorePublicController::class, 'update'])->name('store.update');
});

// -------------------------------------
// Pruebas y consultas
// -------------------------------------
Route::get('consulta', [OrmController::class, 'consulta']);

Route::get('/store', [StoreController::class, 'index'])->name('store.index');
