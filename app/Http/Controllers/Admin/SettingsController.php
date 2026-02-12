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
        $store = auth()->user()->stores()->firstOrFail();
        return view('admin.settings.index', compact('store'));
    }

    public function updateGeneral(UpdateGeneralRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'ConfiguraciÃ³n general actualizada.');
    }

    public function updateAppearance(UpdateAppearanceRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();

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
        $store = auth()->user()->stores()->firstOrFail();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'ConfiguraciÃ³n de pagos actualizada.');
    }

    public function updateShipping(UpdateShippingRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'ConfiguraciÃ³n de envÃ­os actualizada.');
    }

    public function updateTaxes(UpdateTaxesRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'ConfiguraciÃ³n de impuestos actualizada.');
    }

    public function updateNotifications(UpdateNotificationsRequest $request)
    {
        $store = auth()->user()->stores()->firstOrFail();
        $store->update($request->validated());
        return redirect()->back()->with('success', 'ConfiguraciÃ³n de notificaciones actualizada.');
    }
}
