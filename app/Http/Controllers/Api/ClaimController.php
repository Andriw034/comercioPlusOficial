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
<<<<<<< HEAD
        $claim = Claim::with('user')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Claim list',
=======
              $claim = Claim::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' =>  'Claim list',
>>>>>>> 691c95be (comentario)
            'data' => $claim,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
<<<<<<< HEAD
        // No aplica para API
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
<<<<<<< HEAD
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
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
<<<<<<< HEAD
        $claim = Claim::find($id);

        if (!$claim) {
            return response()->json(['message' => 'Reclamo no encontrado'], 404);
        }

        return response()->json($claim);
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
<<<<<<< HEAD
        // No aplica para API
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
<<<<<<< HEAD
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
=======
        //
>>>>>>> 691c95be (comentario)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
<<<<<<< HEAD
        $claim = Claim::find($id);

        if (!$claim) {
            return response()->json(['message' => 'Reclamo no encontrado'], 404);
        }

        $claim->delete();

        return response()->json([
            'message' => 'Reclamo eliminado correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
