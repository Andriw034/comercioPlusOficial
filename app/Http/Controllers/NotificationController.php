<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
      public function index()
    {
        $Notification = Notification::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' => $Notification,
        ]);
    }
}
