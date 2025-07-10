<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StoreWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $stores = Store::with(['user', 'products'])
            ->where('estado', 'activa')
            ->paginate(12);

        $props = [
            'stores' => $stores,
            'title' => 'Tiendas - Comercio Plus',
        ];

        if ($request->header('X-Inertia')) {
            return response()->json([
                'component' => 'Stores/Index',
                'props' => $props,
                'url' => '/stores',
                'version' => null,
            ])->header('X-Inertia', 'true');
        }

        return Inertia::render('Stores/Index', $props);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::with(['user', 'products.category'])
            ->findOrFail($id);

        return Inertia::render('Stores/Show', [
            'store' => $store,
            'title' => $store->name . ' - Comercio Plus'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
