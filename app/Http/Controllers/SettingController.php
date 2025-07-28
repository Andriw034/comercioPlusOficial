<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' =>  $setting,
        ]);
    }

    public function show($key)
    {
        $setting = Setting::where('key', $key)
            ->where('user_id', Auth::id())
            ->first();

        if (!$setting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Configuración no encontrada',
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $setting,
        ]);
    }
}


