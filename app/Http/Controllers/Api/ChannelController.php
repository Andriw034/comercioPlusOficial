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
        $channel = Channel::with('user')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Channels retrieved successfully',
            'data' => $channel,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No aplica para API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'link' => 'required|string|max:255',
        ]);

        $channel = Channel::create($validated);

        return response()->json([
            'message' => 'Canal creado correctamente.',
            'data' => $channel,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json(['message' => 'Canal no encontrado'], 404);
        }

        return response()->json($channel);
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json(['message' => 'Canal no encontrado'], 404);
        }

        $channel->delete();

        return response()->json([
            'message' => 'Canal eliminado correctamente.',
        ]);
    }
}
