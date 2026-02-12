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
    public function index(Request $request)
    {
        $user = $request->user();
        $orden = Order::with('user', 'ordenproducts')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Lista de órdenes',
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
            'store_id' => 'required|exists:stores,id',
            'total' => 'required|numeric|min:0',
            'date' => 'nullable|date',
            'payment_method' => 'required|string|max:255',
            'status' => 'nullable|in:pending,paid,cancelled,completed',
        ]);

        $validated['user_id'] = $request->user()->id;
        if (empty($validated['date'])) {
            $validated['date'] = now();
        }
        if (empty($validated['status'])) {
            $validated['status'] = 'pending';
        }

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
            'store_id' => 'sometimes|exists:stores,id',
            'total' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,paid,cancelled,completed',
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

    /**
     * Historial de órdenes del comerciante (por su tienda)
     */
    public function merchantIndex(Request $request)
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $orders = Order::with('user', 'ordenproducts')
            ->where('store_id', $store->id)
            ->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Lista de órdenes',
            'data' => $orders,
        ]);
    }

    /**
     * Actualizar estado de orden (comerciante)
     */
    public function updateStatus(Request $request, string $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        $store = $request->user()->store()->first();
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,cancelled,completed',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'data' => $order,
        ]);
    }
}
