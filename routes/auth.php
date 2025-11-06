<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController as AuthPasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController as AuthNewPasswordController;

// Auth routes (sin Auth::routes() para evitar duplicados)
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [AuthPasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [AuthPasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthNewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [AuthNewPasswordController::class, 'store'])->name('password.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.post');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
