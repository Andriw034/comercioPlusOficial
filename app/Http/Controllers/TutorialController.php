<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
       public function index()
    {
        $tutorial = Tutorial::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' => $tutorial,
        ]);
    }
}
