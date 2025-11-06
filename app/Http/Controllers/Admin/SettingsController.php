<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeneralRequest;
use App\Http\Requests\Admin\UpdateAppearanceRequest;
use App\Http\Requests\Admin\UpdatePaymentsRequest;
use App\Http\Requests\Admin\UpdateShippingRequest;
use App\Http\Requests\Admin\UpdateTaxesRequest;
use App\Http\Requests\Admin\UpdateNotificationsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        return view('admin.settings.index', compact('store'));
    }

    public function updateGeneral(UpdateGeneralRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'Configuración general actualizada.');
    }

    public function updateAppearance(UpdateAppearanceRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();

        $data = [];

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('stores/' . $store->id . '/logo', 'public');
        }

        if ($request->hasFile('cover')) {
            if ($store->cover_path) {
                Storage::disk('public')->delete($store->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('stores/' . $store->id . '/cover', 'public');
        }

        $store->update($data);
        return redirect()->back()->with('success', 'Apariencia actualizada.');
    }

    public function updatePayments(UpdatePaymentsRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'Configuración de pagos actualizada.');
    }

    public function updateShipping(UpdateShippingRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'Configuración de envíos actualizada.');
    }

    public function updateTaxes(UpdateTaxesRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'Configuración de impuestos actualizada.');
    }

    public function updateNotifications(UpdateNotificationsRequest $request)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene tienda
        if (!$user->stores()->exists()) {
            return redirect()->route('store.create')
                ->with('error', 'Debes crear una tienda antes de acceder a la configuración.');
        }

        $store = $user->stores()->first();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'Configuración de notificaciones actualizada.');
    }
}
