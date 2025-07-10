<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener logo desde settings para rol 'all' o usuario null
        $logoSetting = \App\Models\Setting::where('key', 'logo')->where(function($query) {
            $query->where('role', 'all')->orWhereNull('user_id');
        })->first();

        $logo = $logoSetting ? $logoSetting->value : null;

        // Obtener productos para mostrar
        $products = \App\Models\Product::with('category')->paginate(9);

        return view('admin.dashboard', compact('logo', 'products'));
    }
}
