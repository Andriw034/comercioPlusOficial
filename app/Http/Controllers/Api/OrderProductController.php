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
        $orderProducts = OrderProduct::with('order', 'product')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Order products retrieved successfully',
            'data' => $orderProducts,
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
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $orderProduct = OrderProduct::create($validated);

        return response()->json([
            'message' => 'Order product created successfully.',
            'data' => $orderProduct,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orderProduct = OrderProduct::with('order', 'product')->find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        return response()->json($orderProduct);
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
        $orderProduct = OrderProduct::find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $validated = $request->validate([
            'order_id' => 'sometimes|exists:orders,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $orderProduct->update($validated);

        return response()->json([
            'message' => 'Order product updated successfully.',
            'data' => $orderProduct,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $orderProduct = OrderProduct::find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $orderProduct->delete();

        return response()->json([
            'message' => 'Order product deleted successfully.',
        ]);
    }
}
