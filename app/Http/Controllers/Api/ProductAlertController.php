<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAlert;
use Illuminate\Http\Request;

class ProductAlertController extends Controller
{
    public function mine(Request $request, Product $product)
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user() ?? $request->user();
        if (! $user) {
            return response()->json([
                'data' => null,
                'following' => false,
            ]);
        }

        $alert = ProductAlert::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        return response()->json([
            'data' => $alert,
            'following' => $alert !== null,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'target_price' => 'required|numeric|min:0',
        ]);

        if (strtolower((string) $product->status) !== 'active') {
            return response()->json([
                'message' => 'El producto no está disponible para alertas',
            ], 422);
        }

        $alert = ProductAlert::query()->updateOrCreate(
            [
                'user_id' => (int) $request->user()->id,
                'product_id' => (int) $product->id,
            ],
            [
                'target_price' => (float) $validated['target_price'],
                'is_triggered' => false,
                'triggered_at' => null,
            ],
        );

        return response()->json([
            'status' => 'ok',
            'data' => $alert,
        ], 201);
    }

    public function destroy(Request $request, Product $product)
    {
        ProductAlert::query()
            ->where('user_id', (int) $request->user()->id)
            ->where('product_id', (int) $product->id)
            ->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Alerta eliminada',
        ]);
    }
}
