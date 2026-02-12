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
     * Mostrar formulario para crear tienda (wizard bÃ¡sico).
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

        // Validaciones bÃ¡sicas
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|size:7', // ej. #ff6600
        ]);

        // Generar slug Ãºnico
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        // Crear store
        $store = Store::create([
            'user_id'       => $user->id,
            'name'          => $data['name'],
            'slug'          => $slug,
            'description'   => $data['description'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'estado'        => 'activa',
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Tienda creada correctamente.');
    }

    /**
     * Mostrar la UI de apariencia (logo / portada).
     * Ruta esperada: route('admin.store.appearance')
     */
    public function appearance()
    {
        $user  = Auth::user();
        $store = $user->stores()->first();

        if (!$store) {
            return redirect()->route('store.create')->with('info', 'Crea tu tienda antes de personalizarla.');
        }

        return view('admin.store.appearance', compact('store')); // resources/views/admin/store/appearance.blade.php
    }

    /**
     * Actualizar logo y fondo (cover) de la tienda.
     * Ruta esperada: route('admin.store.update_appearance') [POST or PUT]
     *
     * Inputs:
     *  - logo (file image)
     *  - cover (file image)
     */
    public function updateAppearance(Request $request)
    {
        $user  = Auth::user();
        $store = $user->stores()->firstOrFail();

        $request->validate([
            'logo'  => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,svg'],
            'cover' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png'],
        ]);

        $directory = "stores/{$store->id}";

        // Logo
        if ($request->hasFile('logo')) {
            if ($store->logo_path && Storage::disk('public')->exists($store->logo_path)) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $store->logo_path = $request->file('logo')->store($directory, 'public');
        }

        // Portada (cover)
        if ($request->hasFile('cover')) {
            if ($store->cover_path && Storage::disk('public')->exists($store->cover_path)) {
                Storage::disk('public')->delete($store->cover_path);
            }
            $store->cover_path = $request->file('cover')->store($directory, 'public');
        }

        $store->save();

        return redirect()
            ->route('admin.store.appearance')
            ->with('status', 'Apariencia actualizada correctamente.');
    }

    /**
     * Eliminar la tienda del usuario (soft delete).
     * Ruta esperada: route('admin.store.destroy') [DELETE]
     */
    public function destroy()
    {
        $user  = Auth::user();
        $store = $user->stores()->firstOrFail();

        // Soft delete de la tienda
        $store->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Tu tienda ha sido eliminada.');
    }
}
