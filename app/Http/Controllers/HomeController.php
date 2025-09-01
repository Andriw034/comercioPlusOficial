<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Puedes pasar datos de portada si quieres
        return view('welcome');
    }
}
