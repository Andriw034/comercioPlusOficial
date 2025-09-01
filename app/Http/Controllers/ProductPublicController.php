<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductPublicController extends Controller
{
    public function show(int $id)
    {
        $product = Product::with('store', 'category')->findOrFail($id);
        return view('products.show', compact('product'));
    }
}
