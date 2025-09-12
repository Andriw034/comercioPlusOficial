<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartProduct;
use Illuminate\Http\Request;

class CartProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = CartProduct::with('cart', 'product')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Data retrieved successfully',
            'data' => $cart,
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
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $cartProduct = CartProduct::create($validated);

        return response()->json([
            'message' => 'Producto agregado al carrito correctamente.',
            'data' => $cartProduct,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        return response()->json($cartProduct);
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
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        $validated = $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $cartProduct->update($validated);

        return response()->json([
            'message' => 'Producto en carrito actualizado correctamente.',
            'data' => $cartProduct,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        $cartProduct->delete();

        return response()->json([
            'message' => 'Producto en carrito eliminado correctamente.',
        ]);
    }
}
