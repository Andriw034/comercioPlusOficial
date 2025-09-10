<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $channel = Channel::with('user')->get();
=======
              $channel = Channel::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;
>>>>>>> 691c95be (comentario)

        return response()->json([
            'status' => 'ok',
            'message' => 'Channels retrieved successfully',
<<<<<<< HEAD
            'data' => $channel,
=======
            'data' =>  $channel,
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
            'type' => 'required|string|max:255',
            'link' => 'required|string|max:255',
        ]);

        $channel = Channel::create($validated);

        return response()->json([
            'message' => 'Canal creado correctamente.',
            'data' => $channel,
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
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json(['message' => 'Canal no encontrado'], 404);
        }

        return response()->json($channel);
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
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json(['message' => 'Canal no encontrado'], 404);
        }

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'link' => 'required|string|max:255',
        ]);

        $channel->update($validated);

        return response()->json([
            'message' => 'Canal actualizado correctamente.',
            'data' => $channel,
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
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json(['message' => 'Canal no encontrado'], 404);
        }

        $channel->delete();

        return response()->json([
            'message' => 'Canal eliminado correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
