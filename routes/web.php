<?php

use App\Http\Controllers\OrmController;
use App\Http\Controllers\PruebaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('consulta', [OrmController::class, 'consulta']);

Route::get('/admin', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('admin.dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');


// Rutas de autenticación existentes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);


// Rutas para restablecimiento de contraseña
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Mini tienda
    Route::get('/store', [\App\Http\Controllers\StoreController::class, 'index'])->name('store.index');
    Route::post('/store/create', [\App\Http\Controllers\StoreController::class, 'create'])->name('store.create');

// Productos
Route::resource('products', \App\Http\Controllers\ProductController::class);

// Categorías
Route::resource('categories', \App\Http\Controllers\CategoryController::class);
});

Route::resource('users', UserController::class);

Route::get('/', function () {
    return view('welcome'); // Cambia "welcome" por la vista que tengas, si es otra
});
