<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $location = Location::with('user')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Locations retrieved successfully',
            'data' => $location,
=======
        $location = Location::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
              'message' => 'Locations retrieved successfully',
            'data' =>  $location,
>>>>>>> 691c95be (comentario)
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
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Ubicación creada correctamente.',
            'data' => $location,
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
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'Ubicación no encontrada'], 404);
        }

        return response()->json($location);
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
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'Ubicación no encontrada'], 404);
        }

        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Ubicación actualizada correctamente.',
            'data' => $location,
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
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'Ubicación no encontrada'], 404);
        }

        $location->delete();

        return response()->json([
            'message' => 'Ubicación eliminada correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
