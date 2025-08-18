<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Store;

class StoreController extends Controller
{
    /**
     * Mostrar la tienda del usuario autenticado.
     */
    public function index()
    {
        $store = Store::where('user_id', Auth::id())->first();

        if (! $store) {
            return redirect()->route('store.create')->with('info', 'Aún no has creado tu tienda.');
        }

        return view('store.index', compact('store'));
    }

    /**
     * Mostrar formulario de creación de tienda.
     */
    public function create()
    {
        $existingStore = Store::where('user_id', Auth::id())->first();

        if ($existingStore) {
            return redirect()->route('store.index')->with('warning', 'Ya tienes una tienda creada.');
        }

        return view('store.create');
    }

    /**
     * Guardar una nueva tienda.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',     // 👈 usa "description" (EN)
            'logo'          => 'nullable|image|max:2048',
            'cover_image'   => 'nullable|image|max:4096',
            'primary_color' => 'nullable|string|max:7', // #RRGGBB
        ]);

        // Generar slug único
        $slug = Str::slug($validated['name']);
        if (Store::where('slug', $slug)->exists()) {
            $slug .= '-' . substr(Str::uuid(), 0, 6);
        }

        // Subir archivos si existen
        $logoPath  = $request->hasFile('logo')
            ? $request->file('logo')->store('logos', 'public')
            : null;

        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        // Crear tienda
        Store::create([
            'user_id'       => Auth::id(),
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null, // 👈 columna real en BD
            'logo'          => $logoPath,
            'cover_image'   => $coverPath,
            'primary_color' => $validated['primary_color'] ?? '#FF6000',
            'slug'          => $slug,
        ]);

        return redirect()->route('products.create')->with('status', 'Tienda creada exitosamente');
    }

    /**
     * Actualizar una tienda existente del usuario.
     */
    public function update(Request $request)
    {
        $store = auth()->user()->store ?? abort(404);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',     // 👈 usa "description" (EN)
            'logo'          => 'nullable|image|max:2048',
            'cover_image'   => 'nullable|image|max:4096',
            'primary_color' => 'nullable|string|max:7',
        ]);

        // Subidas opcionales
        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $store->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        // Campos de texto
        $store->name          = $validated['name'];
        $store->description   = $validated['description'] ?? $store->description; // 👈 columna real
        $store->primary_color = $validated['primary_color'] ?? $store->primary_color;

        // Si cambió el nombre, actualizar slug (opcional)
        if ($store->isDirty('name')) {
            $newSlug = Str::slug($store->name);
            if (Store::where('slug', $newSlug)->where('id', '!=', $store->id)->exists()) {
                $newSlug .= '-' . substr(Str::uuid(), 0, 6);
            }
            $store->slug = $newSlug;
        }

        $store->save();

        return back()->with('status', 'Configuración de tienda actualizada');
    }
}
