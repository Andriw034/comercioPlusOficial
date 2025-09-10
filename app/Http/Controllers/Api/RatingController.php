<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $rating = Rating::with('user', 'product')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de calificaciones',
            'data' => $rating,
        ]);
=======
        $rating = Rating::included()
        ->filter()
        ->sort()
        ->getOrPaginate();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',       
             'data' => $rating,
   ]);
>>>>>>> 691c95be (comentario)
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
        //
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
