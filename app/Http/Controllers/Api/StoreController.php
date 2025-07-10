<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'ok',
            'data' => $stores
        ], 200);
    }

    public function store(Request $request)
    {
        $request->merge([
            'slug' => $request->filled('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'))
        ]);

        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:255',
            'slug'               => 'required|string|max:255|unique:stores,slug',
            'description'        => 'nullable|string',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'categoria_principal'=> 'nullable|string|max:255',
            'primary_color'      => 'nullable|string|max:20',
            'text_color'         => 'nullable|string|max:20',
            'button_color'       => 'nullable|string|max:20',
            'background_color'   => 'nullable|string|max:20',
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ], [
            'slug.unique' => 'Ese slug ya está en uso. Intenta con otro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors()
            ], 422);
        }

        $logoPath  = null;
        $coverPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $store = Store::create([
            'user_id'           => Auth::id(),
            'name'              => $request->name,
            'slug'              => $request->slug,
            'description'       => $request->description,
            'direccion'         => $request->direccion,
            'telefono'          => $request->telefono,
            'categoria_principal' => $request->categoria_principal,
            'primary_color'     => $request->input('primary_color', '#FF6000'),
            'text_color'        => $request->input('text_color', '#333333'),
            'button_color'      => $request->input('button_color', '#FF6000'),
            'background_color'  => $request->input('background_color', null),
            'logo'              => $logoPath,
            'cover_image'       => $coverPath,
            'estado'            => 'activa',
            'calificacion_promedio' => 0.00,
        ]);

        $store->refresh();

        return response()->json([
            'message' => 'Tienda creada exitosamente',
            'data'    => [
                'id'           => $store->id,
                'name'         => $store->name,
                'slug'         => $store->slug,
                'logo_url'     => $store->logo_url,
                'cover_url'    => $store->cover_url,
                'primary_color'=> $store->primary_color,
                'text_color'   => $store->text_color,
                'button_color' => $store->button_color,
            ]
        ], 201);
    }

    public function show($id)
    {
        $store = Store::find($id);
        if (!$store) return response()->json(['message' => 'Tienda no encontrada'], 404);

        if ($store->user_id !== Auth::id()) return response()->json(['message' => 'No autorizado'], 403);

        return response()->json(['status' => 'ok', 'data' => $store], 200);
    }

    public function update(Request $request, $id)
    {
        $store = Store::find($id);
        if (!$store) return response()->json(['message' => 'Tienda no encontrada'], 404);
        if ($store->user_id !== Auth::id()) return response()->json(['message' => 'No autorizado'], 403);

        $validator = Validator::make($request->all(), [
            'name'               => 'sometimes|string|max:255',
            'slug'               => 'sometimes|string|max:255|unique:stores,slug,' . $id,
            'description'        => 'nullable|string',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'categoria_principal'=> 'nullable|string|max:255',
            'primary_color'      => 'nullable|string|max:20',
            'text_color'         => 'nullable|string|max:20',
            'button_color'       => 'nullable|string|max:20',
            'background_color'   => 'nullable|string|max:20',
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('cover')) {
            $store->cover_image = $request->file('cover')->store('covers', 'public');
        }

        foreach ([
            'name','slug','description','direccion','telefono','categoria_principal',
            'primary_color','text_color','button_color','background_color'
        ] as $f) {
            if ($request->filled($f)) $store->$f = $request->$f;
        }

        $store->save();
        $store->refresh();

        return response()->json(['message' => 'Tienda actualizada exitosamente', 'data' => $store], 200);
    }

    public function destroy($id)
    {
        $store = Store::find($id);
        if (!$store) return response()->json(['message' => 'Tienda no encontrada'], 404);
        if ($store->user_id !== Auth::id()) return response()->json(['message' => 'No autorizado'], 403);

        $store->delete();
        return response()->json(['message' => 'Tienda eliminada exitosamente'], 200);
    }
}
