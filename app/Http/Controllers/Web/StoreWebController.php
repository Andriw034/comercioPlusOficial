<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreWebController extends Controller
{
    public function index()
    {
        $stores = Store::where('user_id', auth()->id())->latest()->paginate(15);
        return view('web.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('web.stores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['required', 'string', 'max:255', 'unique:stores,slug'],
            'address' => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
        ]);

        $data['user_id'] = auth()->id();

        $store = Store::create($data);

        return redirect()->route('stores.edit', $store)->with('success', 'Tienda creada.');
    }

    public function show(Store $store)
    {
        $this->authorize('view', $store);
        return view('web.stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $this->authorize('update', $store);
        return view('web.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $this->authorize('update', $store);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['required', 'string', 'max:255', 'unique:stores,slug,'.$store->id],
            'address' => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
        ]);

        $store->update($data);

        return back()->with('success', 'Tienda actualizada.');
    }

    public function destroy(Store $store)
    {
        $this->authorize('delete', $store);
        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Tienda eliminada.');
    }
}
