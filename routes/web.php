<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Store;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Public Stores Index (for Inertia)
Route::get('/stores', function () {
    return Inertia::render('Stores/Index', [
        'stores' => Store::paginate(10), // The test requires paginated data
    ]);
})->name('stores.index');

// Protected Dashboard Route
// Corrected middleware to use standard Laravel 'auth' and 'verified' middleware,
// removing the problematic dependency on Jetstream's config.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index', [
            'title' => 'Dashboard - Comercio Plus' // As expected by the test
        ]);
    })->name('dashboard');
});

// Include the backend auth routes
require __DIR__.'/auth.php';
