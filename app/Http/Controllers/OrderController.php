<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

      public function index()
    {
        $orden = Order::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' =>  $orden,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'ordenproducts.product']);

        return view('orders.show', compact('order'));
    }
}
