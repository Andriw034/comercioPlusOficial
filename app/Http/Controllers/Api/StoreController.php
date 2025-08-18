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
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'logo'              => 'nullable|image|max:2048',
            'cover'             => 'nullable|image|max:4096',
            'background'        => 'nullable|image|max:4096',
            'primary_color'     => 'nullable|string|max:7',
            'direccion'         => 'required|string|max:255',
            'telefono'          => 'nullable|string|max:20',
            'horario_atencion'  => 'nullable|string|max:255',
            'categoria_principal' => 'required|string|max:255',
        ]);

        // Slug único
        $slug = Str::slug($validated['name']);
        if (Store::where('slug', $slug)->exists()) {
            $slug .= '-' . substr(Str::uuid(), 0, 6);
        }

        // Subida de imágenes
        $logoPath       = $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : null;
        $coverPath      = $request->hasFile('cover') ? $request->file('cover')->store('covers', 'public') : null;
        $backgroundPath = $request->hasFile('background') ? $request->file('background')->store('backgrounds', 'public') : null;

        // Crear tienda
        Store::create([
            'user_id'            => Auth::id(),
            'name'               => $validated['name'],
            'slug'               => $slug,
            'logo'               => $logoPath,
            'cover'              => $coverPath,
            'background'         => $backgroundPath,
            'primary_color'      => $validated['primary_color'] ?? '#FFA14F',
            'description'        => $validated['description'] ?? null,
            'direccion'          => $validated['direccion'],
            'telefono'           => $validated['telefono'] ?? null,
            'horario_atencion'   => $validated['horario_atencion'] ?? null,
            'categoria_principal'=> $validated['categoria_principal'],
            'calificacion_promedio' => 0, // por defecto
        ]);

        return redirect()->route('products.create')->with('status', 'Tienda creada exitosamente');
    }

    /**
     * Actualizar tienda existente.
     */
    public function update(Request $request)
    {
        $store = auth()->user()->store ?? abort(404);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'logo'              => 'nullable|image|max:2048',
            'cover'             => 'nullable|image|max:4096',
            'background'        => 'nullable|image|max:4096',
            'primary_color'     => 'nullable|string|max:7',
            'direccion'         => 'required|string|max:255',
            'telefono'          => 'nullable|string|max:20',
            'horario_atencion'  => 'nullable|string|max:255',
            'categoria_principal' => 'required|string|max:255',
        ]);

        // Subidas opcionales
        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('cover')) {
            $store->cover = $request->file('cover')->store('covers', 'public');
        }
        if ($request->hasFile('background')) {
            $store->background = $request->file('background')->store('backgrounds', 'public');
        }

        // Actualizar campos de texto
        $store->fill([
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? $store->description,
            'primary_color'      => $validated['primary_color'] ?? $store->primary_color,
            'direccion'          => $validated['direccion'],
            'telefono'           => $validated['telefono'] ?? $store->telefono,
            'horario_atencion'   => $validated['horario_atencion'] ?? $store->horario_atencion,
            'categoria_principal'=> $validated['categoria_principal'],
        ]);

        // Si cambia el nombre, actualizar slug
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
