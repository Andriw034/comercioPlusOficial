<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = Setting::all();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de configuraciones',
            'data' => $setting,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['message' => 'Configuración no encontrada'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $setting,
        ]);
    }

    /**
     * Update settings via API
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'theme_style' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                set_setting($key, $value);
            }
        }

        return response()->json([
            'message' => 'Configuración actualizada correctamente',
            'data' => Setting::all(),
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
