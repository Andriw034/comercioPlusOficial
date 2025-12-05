<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $Notification = Notification::with('user')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Notificaciones',
=======
              $Notification = Notification::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
              'message' => 'Notificaciones',
>>>>>>> 691c95be (comentario)
            'data' => $Notification,
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
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_read' => 'required|boolean',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'message' => 'Notificación creada correctamente.',
            'data' => $notification,
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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        return response()->json($notification);
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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_read' => 'required|boolean',
        ]);

        $notification->update($validated);

        return response()->json([
            'message' => 'Notificación actualizada correctamente.',
            'data' => $notification,
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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notificación eliminada correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
