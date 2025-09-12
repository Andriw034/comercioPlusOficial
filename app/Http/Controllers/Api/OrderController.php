<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orden = Order::with('user', 'ordenproducts')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Lista de ordenes',
            'data' => $orden,
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
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'payment_method' => 'required|string|max:255',
        ]);

        $order = Order::create($validated);

        return response()->json([
            'message' => 'Orden creada correctamente.',
            'data' => $order,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        return response()->json($order);
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
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'store_id' => 'sometimes|exists:stores,id',
            'total' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,processing,completed,cancelled',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Orden actualizada correctamente.',
            'data' => $order,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $order->delete();

        return response()->json([
            'message' => 'Orden eliminada correctamente.',
        ]);
    }
}
