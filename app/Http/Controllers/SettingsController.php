<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\PlaceholderData;

class SettingsController extends Controller
{
    public function storeSettings()
    {
        return view('dashboard.settings.store', [
            'store' => PlaceholderData::store(),
        ]);
    }

    public function storeSettingsSave(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','min:3'],
            'address'     => ['required','string','min:5'],
            'logo'        => ['nullable','url'],
            'cover'       => ['nullable','url'],
            'description' => ['nullable','string'],
        ]);

        // Aquí guardarías en DB. Simulación con flash.
        return back()->with('ok', 'Ajustes guardados (simulado).');
    }
}
