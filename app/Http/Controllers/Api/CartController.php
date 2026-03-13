<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'ok',
                'data' => [],
                'items' => [],
                'total' => 0,
                'count' => 0,
                'cart_id' => null,
            ]);
        }

        $payload = $this->buildCartPayload($cart);
        return response()->json([
            'status' => 'ok',
            'data' => $payload['items'],
            'items' => $payload['items'],
            'total' => $payload['total'],
            'count' => $payload['count'],
            'cart_id' => $payload['id'],
        ]);
    }

    /**
     * Legacy compatible behavior:
     * - POST /cart with product_id + quantity => add item
     * - POST /cart without payload => create or get cart
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->firstOrCreate(['user_id' => (int) $user->id]);

        $hasItemPayload = $request->filled('product_id') && $request->filled('quantity');
        if (!$hasItemPayload) {
            return response()->json($cart, 201);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail((int) $validated['product_id']);
        $qtyToAdd = (int) $validated['quantity'];

        $line = CartProduct::query()
            ->where('cart_id', (int) $cart->id)
            ->where('product_id', (int) $product->id)
            ->first();

        $newQty = $line ? ((int) $line->quantity + $qtyToAdd) : $qtyToAdd;
        if ((int) $product->stock < $newQty) {
            return response()->json([
                'message' => 'Stock insuficiente',
            ], 422);
        }

        if ($line) {
            $line->quantity = $newQty;
            $line->unit_price = (float) $product->price;
            $line->save();
        } else {
            CartProduct::query()->create([
                'cart_id' => (int) $cart->id,
                'product_id' => (int) $product->id,
                'quantity' => $qtyToAdd,
                'unit_price' => (float) $product->price,
            ]);
        }

        return response()->json([
            'message' => 'Producto agregado al carrito',
            'cart' => $this->buildCartPayload($cart->fresh()),
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        return response()->json($cart);
    }

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        $cart->delete();
        return response()->json(['message' => 'Carrito eliminado']);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        return response()->json($this->buildCartPayload($cart));
    }

    public function count(): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->first();

        if (!$cart) {
            return response()->json(['count' => 0]);
        }

        $count = (int) CartProduct::query()->where('cart_id', (int) $cart->id)->sum('quantity');
        return response()->json(['count' => $count]);
    }

    public function clear(): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::query()->where('user_id', (int) $user->id)->first();

        if ($cart) {
            CartProduct::query()->where('cart_id', (int) $cart->id)->delete();
        }

        return response()->json([
            'message' => 'Carrito vaciado',
        ]);
    }

    private function buildCartPayload(Cart $cart): array
    {
        $items = CartProduct::query()
            ->with(['product.store', 'product.category'])
            ->where('cart_id', (int) $cart->id)
            ->get()
            ->map(function (CartProduct $line) {
                $unitPrice = (float) ($line->unit_price ?? $line->product?->price ?? 0);
                $quantity = (int) $line->quantity;

                return [
                    'id' => (int) $line->id,
                    'cart_id' => (int) $line->cart_id,
                    'product_id' => (int) $line->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($unitPrice * $quantity, 2),
                    'product' => $line->product,
                ];
            })
            ->values();

        return [
            'id' => (int) $cart->id,
            'items' => $items,
            'total' => round((float) $items->sum('subtotal'), 2),
            'count' => (int) $items->sum('quantity'),
        ];
    }
}
