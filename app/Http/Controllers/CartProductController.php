<?php

namespace App\Http\Controllers;

use App\Models\CartProduct;
use Illuminate\Http\Request;

class CartProductController extends Controller
{
          public function index()
    {
        $cartproduct = CartProduct::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' => $cartproduct,
        ]);
    }
}

