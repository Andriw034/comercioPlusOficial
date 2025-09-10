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
<<<<<<< HEAD
        $cart = CartProduct::with('cart', 'product')->get();
=======
              $cart = CartProduct::included() 
        ->filter()
        ->sort()
        ->getOrPaginate();;
>>>>>>> 691c95be (comentario)

        return response()->json([
            'status' => 'ok',
            'message' => 'Data retrieved successfully',
<<<<<<< HEAD
            'data' => $cart,
=======
            'data' =>  $cart,
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
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        return response()->json($cartProduct);
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
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        $validated = $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $cartProduct->update($validated);

        return response()->json([
            'message' => 'Producto en carrito actualizado correctamente.',
            'data' => $cartProduct,
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
        $cartProduct = CartProduct::find($id);

        if (!$cartProduct) {
            return response()->json(['message' => 'Producto en carrito no encontrado'], 404);
        }

        $cartProduct->delete();

        return response()->json([
            'message' => 'Producto en carrito eliminado correctamente.',
        ]);
=======
        //
>>>>>>> 691c95be (comentario)
    }
}
