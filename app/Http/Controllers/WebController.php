<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class WebController extends Controller
{
    public function welcome(): Response
    {
        return Inertia::render('Welcome', [
            'title'       => 'Bienvenido a Comercio Plus',
            'description' => 'La plataforma de e-commerce para tiendas de repuestos de motos',
        ]);
    }

    public function dashboard(): Response
    {
        // Asegúrate de crear resources/js/Pages/Dashboard/Index.vue más adelante
        return Inertia::render('Dashboard/Index', [
            'title' => 'Dashboard - Comercio Plus',
        ]);
    }
}
