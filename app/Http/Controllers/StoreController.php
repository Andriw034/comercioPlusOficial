<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\PublicStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function create()
    {
        return view('store.create');
    }

    public function store(Request $request)
    {
        // 1) Validación
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Subida desde PC
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'cover'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],

            // Desde URL (opcional)
            'logo_url'    => ['nullable', 'url'],
            'cover_url'   => ['nullable', 'url'],
        ]);

        $user = Auth::user();

        // 2) Slug único
        $baseSlug = Str::slug($data['name']);
        $slug = $this->uniqueSlug($baseSlug);

        // 3) Crear/actualizar Store del usuario
        $store = Store::firstOrNew(['user_id' => $user->id]);
        $justCreated = !$store->exists;

        $store->fill([
            'name'        => $data['name'],
            'slug'        => $justCreated ? $slug : ($store->slug ?: $slug),
            'description' => $data['description'] ?? null,
        ]);
        $store->user_id = $user->id;
        $store->save();

        // 4) Guardar assets
        $brandingDir = "stores/{$store->id}/branding";
        $logoPath  = $this->saveFromUrlOrFile($request, 'logo',  'logo_url',  $brandingDir) ?: $store->logo;
        $coverPath = $this->saveFromUrlOrFile($request, 'cover', 'cover_url', $brandingDir) ?: $store->cover;

        // 5) Actualizar columnas reales (logo, cover)
        $store->update([
            'logo'  => $logoPath,
            'cover' => $coverPath,
        ]);

        // 6) Sincronizar PublicStore
        PublicStore::updateOrCreate(
            ['store_id' => $store->id],
            [
                'user_id'            => $user->id,
                'name'               => $store->name,
                'nombre_tienda'      => $store->name,
                'slug'               => $store->slug,
                'descripcion'        => $store->description,
                'logo'               => $logoPath,
                'cover'              => $coverPath,
                'estado'             => 'activa',  // según tu enum
                'categoria_principal'=> null,
            ]
        );

        // 7) Branding para la UI
        $branding = [
            'store_id'   => $store->id,
            'store_name' => $store->name,
            'logo_url'   => $logoPath  ? Storage::disk('public')->url($logoPath)  : null,
            'cover_url'  => $coverPath ? Storage::disk('public')->url($coverPath) : null,
        ];

        // 8) Redirección al listado de productos del panel
        return redirect()
            ->route('admin.products.index')
            ->with('success', '¡Tu tienda fue creada/actualizada con éxito!')
            ->with('store_branding', $branding);
    }

    private function saveFromUrlOrFile(Request $request, string $fileField, string $urlField, string $dir): ?string
    {
        if ($request->hasFile($fileField)) {
            $file = $request->file($fileField);
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'png');
            $name = Str::uuid()->toString() . '.' . $ext;
            return $file->storeAs($dir, $name, 'public');
        }

        $url = trim((string) $request->input($urlField));
        if ($url !== '') {
            try {
                $contents = @file_get_contents($url);
                if ($contents !== false) {
                    $extGuess = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                    $ext = in_array(strtolower($extGuess), ['png','jpg','jpeg','webp']) ? strtolower($extGuess) : 'jpg';
                    $name = Str::uuid()->toString() . '.' . $ext;
                    $path = "{$dir}/{$name}";
                    Storage::disk('public')->put($path, $contents);
                    return $path;
                }
            } catch (\Throwable $e) {}
        }
        return null;
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug ?: Str::uuid()->toString();
        if (!Store::where('slug', $slug)->exists()) return $slug;

        $i = 2;
        while (Store::where('slug', "{$slug}-{$i}")->exists()) $i++;
        return "{$slug}-{$i}";
    }
}
