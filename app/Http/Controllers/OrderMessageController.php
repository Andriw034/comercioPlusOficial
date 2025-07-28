<?php

namespace App\Http\Controllers;

use App\Models\OrderMessage;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
     public function index()
    {
        $ordenmensaje = OrderMessage::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' =>  $ordenmensaje,
        ]);
    }
}
