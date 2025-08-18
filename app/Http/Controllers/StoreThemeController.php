<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreThemeController extends Controller
{
    public function index(Store $store)
    {
        $themes = $store->themes()->orderBy('is_active', 'desc')->get();
        return view('store.themes.index', compact('store', 'themes'));
    }

    public function create(Store $store)
    {
        return view('store.themes.create', compact('store'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'font_family' => 'required|string|max:255',
            'custom_css' => 'nullable|string',
            'background_image' => 'nullable|image|max:2048',
            'logo' => 'nullable|image|max:1024',
            'settings' => 'nullable|array',
        ]);

        $theme = new StoreTheme($validated);
        $theme->store_id = $store->id;

        if ($request->hasFile('background_image')) {
            $theme->background_image = $request->file('background_image')->store('themes/backgrounds', 'public');
        }

        if ($request->hasFile('logo')) {
            $theme->logo = $request->file('logo')->store('themes/logos', 'public');
        }

        $theme->save();

        return redirect()->route('stores.themes.index', $store)
            ->with('success', 'Theme created successfully');
    }

    public function edit(Store $store, StoreTheme $theme)
    {
        return view('store.themes.edit', compact('store', 'theme'));
    }

    public function update(Request $request, Store $store, StoreTheme $theme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'font_family' => 'required|string|max:255',
            'custom_css' => 'nullable|string',
            'background_image' => 'nullable|image|max:2048',
            'logo' => 'nullable|image|max:1024',
            'settings' => 'nullable|array',
        ]);

        if ($request->hasFile('background_image')) {
            if ($theme->background_image) {
                Storage::disk('public')->delete($theme->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('themes/backgrounds', 'public');
        }

        if ($request->hasFile('logo')) {
            if ($theme->logo) {
                Storage::disk('public')->delete($theme->logo);
            }
            $validated['logo'] = $request->file('logo')->store('themes/logos', 'public');
        }

        $theme->update($validated);

        return redirect()->route('stores.themes.index', $store)
            ->with('success', 'Theme updated successfully');
    }

    public function activate(Store $store, StoreTheme $theme)
    {
        $store->themes()->update(['is_active' => false]);
        $theme->update(['is_active' => true]);

        return redirect()->route('stores.themes.index', $store)
            ->with('success', 'Theme activated successfully');
    }

    public function destroy(Store $store, StoreTheme $theme)
    {
        if ($theme->background_image) {
            Storage::disk('public')->delete($theme->background_image);
        }
        if ($theme->logo) {
            Storage::disk('public')->delete($theme->logo);
        }

        $theme->delete();

        return redirect()->route('stores.themes.index', $store)
            ->with('success', 'Theme deleted successfully');
    }
}
