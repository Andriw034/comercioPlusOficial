<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $tutorial = Tutorial::all();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de tutoriales',
=======
         $tutorial = Tutorial::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
>>>>>>> 691c95be (comentario)
            'data' => $tutorial,
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
            'language' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $tutorial = Tutorial::create($validated);

        return response()->json([
            'message' => 'Tutorial creado correctamente.',
            'data' => $tutorial,
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
        $tutorial = Tutorial::find($id);

        if (!$tutorial) {
            return response()->json(['message' => 'Tutorial no encontrado'], 404);
        }

        return response()->json($tutorial);
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
        $tutorial = Tutorial::find($id);

        if (!$tutorial) {
            return response()->json(['message' => 'Tutorial no encontrado'], 404);
        }

        $validated = $request->validate([
            'language' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $tutorial->update($validated);

        return response()->json([
            'message' => 'Tutorial actualizado correctamente.',
            'data' => $tutorial,
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
        $tutorial = Tutorial::find($id);

        if (!$tutorial) {
            return response()->json(['message' => 'Tutorial no encontrado'], 404);
        }

        $tutorial->delete();

        return response()->json([
            'message' => 'Tutorial eliminado correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
