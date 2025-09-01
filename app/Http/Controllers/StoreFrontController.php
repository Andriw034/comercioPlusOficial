<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class StoreFrontController extends Controller
{
    public function show(string $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        $products = Product::where('store_id', $store->id)->latest()->paginate(24);
        $categories = Category::orderBy('name')->get()->keyBy('id');
        return view('store.show', compact('store', 'products', 'categories'));
    }
}
