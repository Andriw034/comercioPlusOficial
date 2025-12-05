<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $claim = Claim::included()
            ->filter()
            ->sort()
            ->getOrPaginate();

        return response()->json([
            'status' => 'ok',
            'message' => 'Claim list',
            'data' => $claim,
        ]);
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

        $validated = $request->validate([
            'message' => 'required|string',
            'date' => 'required|date',
            'contact_method' => 'required|string|in:email,phone',
        ]);

        $claim = Claim::create($validated);

        return response()->json([
            'message' => 'Reclamo creado correctamente.',
            'data' => $claim,
        ], 201);

        //

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $claim = Claim::find($id);

        if (!$claim) {
            return response()->json(['message' => 'Reclamo no encontrado'], 404);
        }

        return response()->json($claim);

        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        // No aplica para API


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $claim = Claim::find($id);

        if (!$claim) {
            return response()->json(['message' => 'Reclamo no encontrado'], 404);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'date' => 'required|date',
            'contact_method' => 'required|string|in:email,phone',
        ]);

        $claim->update($validated);

        return response()->json([
            'message' => 'Reclamo actualizado correctamente.',
            'data' => $claim,
        ]);

        //

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $claim = Claim::find($id);

        if (!$claim) {
            return response()->json(['message' => 'Reclamo no encontrado'], 404);
        }

        $claim->delete();

        return response()->json([
            'message' => 'Reclamo eliminado correctamente.',
        ]);

        //

    }
}
