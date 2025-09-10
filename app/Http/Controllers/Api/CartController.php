<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
<<<<<<< HEAD
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
=======
use Illuminate\Http\Request;
>>>>>>> 691c95be (comentario)

class CartController extends Controller
{
    /**
<<<<<<< HEAD
     * Display the user's cart items.
     */
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'ok',
                'data' => []
            ]);
        }

        $cartItems = CartProduct::with(['product.store', 'product.category'])
            ->where('cart_id', $cart->id)
            ->get();

        return response()->json([
            'status' => 'ok',
            'data' => $cartItems
=======
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
>>>>>>> 691c95be (comentario)
        ]);
    }

    /**
<<<<<<< HEAD
     * Create or get user's cart.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        return response()->json($cart, 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->find($id);
        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }
        // Para este test, no hay campos que actualizar aparte del user_id
        return response()->json($cart);
    }

    /**
     * Remove item from cart.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->find($id);
        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }
        $cart->delete();
        return response()->json(['message' => 'Carrito eliminado']);
    }

    /**
     * Show a specific cart
     */
    public function show($id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->find($id);
        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }
        return response()->json($cart);
    }

    /**
     * Get cart items count.
     */
    public function count()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['count' => 0]);
        }

        $count = CartProduct::where('cart_id', $cart->id)->sum('quantity');

        return response()->json(['count' => $count]);
    }

    /**
     * Clear all items from cart.
     */
    public function clear()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            CartProduct::where('cart_id', $cart->id)->delete();
        }

        return response()->json([
            'message' => 'Carrito vaciado'
        ]);
=======
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
>>>>>>> 691c95be (comentario)
    }
}
