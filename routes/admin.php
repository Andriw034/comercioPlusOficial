<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\AdminController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('products', ProductController::class);
Route::post('products/{product}/toggle-promotion', [ProductController::class, 'togglePromotion'])->name('products.toggle-promotion');

Route::resource('categories', CategoryController::class);

Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update.general');
Route::post('settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.update.appearance');
Route::post('settings/payments', [SettingsController::class, 'updatePayments'])->name('settings.update.payments');
Route::post('settings/shipping', [SettingsController::class, 'updateShipping'])->name('settings.update.shipping');
Route::post('settings/taxes', [SettingsController::class, 'updateTaxes'])->name('settings.update.taxes');
Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update.notifications');

Route::resource('users', AdminController::class);
