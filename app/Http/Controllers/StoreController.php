<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario para crear tienda (wizard básico).
     * Ruta esperada: route('store.create')
     */
    public function create()
    {
        // Si el usuario ya tiene tienda, redirigir al panel
        $user = Auth::user();
        if ($user->stores()->exists()) {
            return redirect()->route('admin.dashboard')->with('info', 'Ya tienes una tienda.');
        }

        return view('store.create'); // resources/views/store/create.blade.php
    }

    /**
     * Guardar tienda nueva.
     * Ruta esperada: route('store.store') [POST]
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validaciones básicas
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|size:7', // ej. #ff6600
        ]);

        // Generar slug único
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        // Crear store
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'estado' => 'activa',
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Tienda creada correctamente.');
    }

    /**
     * Mostrar la UI de apariencia (logo / portada).
     * Ruta esperada: route('admin.store.appearance')
     */
    public function appearance()
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (! $store) {
            return redirect()->route('store.create')->with('info', 'Crea tu tienda antes de personalizarla.');
        }

        return view('admin.store.appearance', compact('store')); // resources/views/admin/store/appearance.blade.php
    }

    /**
     * Actualizar logo y fondo (cover) de la tienda.
     * Ruta esperada: route('admin.store.update_appearance') [POST or PUT]
     *
     * Recibe inputs:
     *  - logo (file image)
     *  - cover (file image)
     */
    public function updateAppearance(Request $request)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (! $store) {
            return redirect()->route('store.create')->with('error', 'Crea tu tienda antes de personalizarla.');
        }

        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096', // cover puede ser más grande
            'primary_color' => 'nullable|string|size:7',
        ]);

        // Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('stores/logos', 'public');

            // Borrar logo anterior si existe
            if ($store->logo_path) {
                try {
                    Storage::disk('public')->delete($store->logo_path);
                } catch (\Throwable $e) { /* ignore */ }
            }

            $store->logo_path = $path;
        }

        // Cover / background
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $path = $file->store('stores/covers', 'public');

            // Borrar cover anterior si existe
            if ($store->background_path) {
                try {
                    Storage::disk('public')->delete($store->background_path);
                } catch (\Throwable $e) { /* ignore */ }
            }

            $store->background_path = $path;
        }

        if (isset($validated['primary_color'])) {
            $store->primary_color = $validated['primary_color'];
        }

        $store->save();

        return redirect()->route('admin.store.appearance')->with('success', 'Apariencia actualizada correctamente.');
    }
}
