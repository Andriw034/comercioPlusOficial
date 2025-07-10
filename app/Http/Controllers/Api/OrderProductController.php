<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class OrderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ordenProduct = OrderProduct::with('order', 'product')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Lista de productos de orden',
            'data' => $ordenProduct,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No aplica para API - este método es para interfaces web
        return response()->json(['message' => 'Método no disponible en API'], 405);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $orderProduct = OrderProduct::create($validated);

        return response()->json([
            'message' => 'Producto agregado a la orden correctamente',
            'data' => $orderProduct->load('order', 'product'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderProduct $orderProduct)
    {
        return response()->json([
            'status' => 'ok',
            'data' => $orderProduct->load('order', 'product'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // No aplica para API - este método es para interfaces web
        return response()->json(['message' => 'Método no disponible en API'], 405);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderProduct $orderProduct)
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $orderProduct->update($validated);

        return response()->json([
            'message' => 'Producto de orden actualizado correctamente',
            'data' => $orderProduct->load('order', 'product'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderProduct $orderProduct)
    {
        $orderProduct->delete();

        return response()->json([
            'message' => 'Producto removido de la orden correctamente',
        ]);
    }
}
