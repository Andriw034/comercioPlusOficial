<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
              $cart = Cart::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;

        return response()->json([
            'status' => 'ok',
            'message' =>  'Cart retrieved successfully',
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
            'user_id' => 'required|exists:users,id',
        ]);

        $cart = Cart::create($validated);

        return response()->json([
            'message' => 'Carrito creado correctamente.',
            'data' => $cart,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        return response()->json($cart);
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
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $cart->update($validated);

        return response()->json([
            'message' => 'Carrito actualizado correctamente.',
            'data' => $cart,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Carrito eliminado correctamente.',
        ]);
    }
}
