<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Puedes pasar datos aquí si quieres
        return view('dashboard');
    }
}
