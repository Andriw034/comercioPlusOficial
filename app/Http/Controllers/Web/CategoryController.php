<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display the specified category with its products.
     */
    public function show(Category $category)
    {
        // Load category with products and store
        $category->load(['products' => function($query) {
            $query->where('status', 'active')
                  ->with('store')
                  ->orderBy('created_at', 'desc');
        }, 'store']);

        // Increment category popularity if needed
        // $category->increment('popularity');

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'products' => $category->products,
            'title' => $category->name . ' - ' . ($category->store->name ?? 'Comercio Plus')
        ]);
    }
}
