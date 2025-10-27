<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class StatsPageController extends Controller
{
    public function __construct()
    {
        // Requiere login; agrega otros middlewares si usas roles/permisos
        $this->middleware('auth');
        // $this->middleware('verified');           // si usas verificación de email
        // $this->middleware('role:admin');        // si usas Spatie Permissions y solo admin ve estadísticas
    }

    public function index()
    {
        // Renderiza la vista creada en el paso anterior
        return view('admin.stats.index');
    }
}
