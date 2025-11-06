<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('catalogo', ['products' => $products]);
    }
}
