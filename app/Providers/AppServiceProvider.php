<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pasar categorías de la tienda al sidebar
        View::composer('layouts.partials.sidebar', function ($view) {
            $store = auth()->user()?->store;

            $cats = $store
                ? Category::where('store_id', $store->id)
                    ->orderBy('name')
                    ->get()
                : collect();

            $view->with('sidebarCategories', $cats);
        });
    }
}
