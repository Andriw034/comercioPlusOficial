<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AIController extends Controller
{
    public function themePage()
    {
        return view('dashboard.ai.theme');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'shopName' => ['required','string'],
            'logo'     => ['required','string'], // Data URI o URL (simulado)
            'cover'    => ['required','string'], // Data URI o URL (simulado)
        ]);

        // Simulación IA: devuelve paleta fija
        return response()->json([
            'primaryColor'    => '#FF6A2E',
            'secondaryColor'  => '#FF9156',
            'backgroundColor' => '#FFF7F2',
            'textColor'       => '#0F172A',
        ]);
    }
}
