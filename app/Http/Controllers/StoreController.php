<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class StoreController extends Controller
{
    public function index()
    {
        // Obtener configuración actual de la tienda
        $store = Setting::where('key', 'store_config')->first();
        $storeData = $store ? json_decode($store->value) : null;

        return view('store.index', ['store' => $storeData]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ]);

        $store = Setting::firstOrNew(['key' => 'store_config']);

        $storeData = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('logo')) {
            // Guardar logo y obtener ruta
            $path = $request->file('logo')->store('public/store_logos');
            // Guardar solo la ruta relativa sin 'public/'
            $storeData['logo'] = str_replace('public/', '', $path);
        } else {
            // Mantener logo anterior si existe
            $existingData = $store->value ? json_decode($store->value, true) : [];
            if (isset($existingData['logo'])) {
                $storeData['logo'] = $existingData['logo'];
            }
        }

        $store->value = json_encode($storeData);
        $store->save();

        return redirect()->route('store.index')->with('success', 'Configuración de la tienda guardada correctamente.');
    }
}
