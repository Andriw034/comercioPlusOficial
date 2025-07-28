<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    
      public function index()
    {
        $role = Role::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' =>  $role,
        ]);
    }
}
