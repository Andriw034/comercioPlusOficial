<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandingThemeRequest;
use App\Models\Store;
use App\Services\ColorPaletteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingController extends Controller
{
    public function show(Request $request)
    {
        $store = Store::where('user_id', $request->user()->id)->first();
        $colors = [
            'primaryColor'   => $store->primary_color ?? '#FFA14F',
            'backgroundColor'=> $store->background_color ?? '#ffffff',
            'textColor'      => $store->text_color ?? '#111827',
        ];
        return view('dashboard.branding', compact('store','colors'));
    }

    public function generate(BrandingThemeRequest $request, ColorPaletteService $palette)
    {
        $user = $request->user();

        // Guardar imágenes
        $logoPath  = $request->file('logo')->store('stores/'. $user->id .'/branding', 'public');
        $coverPath = $request->file('cover')->store('stores/'. $user->id .'/branding', 'public');

        $logoAbs  = storage_path('app/public/' . $logoPath);
        $coverAbs = storage_path('app/public/' . $coverPath);

        // Generar paleta ("IA")
        $colors = $palette->generateTheme($logoAbs, $coverAbs);

        // Upsert tienda del usuario
        $store = Store::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name'             => $request->string('shopName'),
                'slug'             => Str::slug($request->string('shopName')),
                'logo_path'        => $logoPath,
                'cover_path'       => $coverPath,
                'primary_color'    => $colors['primaryColor'],
                'background_color' => $colors['backgroundColor'],
                'text_color'       => $colors['textColor'],
            ]
        );

        return back()->with(['colors' => $colors, 'store' => $store]);
    }
}
