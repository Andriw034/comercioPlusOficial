<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SimpleReceipt;
use App\Services\SimpleReceiptPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimpleReceiptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $receipts = SimpleReceipt::where('store_id', $store->id)
            ->with('order:id,total,created_at')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $receipts->items(),
            'meta' => [
                'current_page' => $receipts->currentPage(),
                'per_page'     => $receipts->perPage(),
                'total'        => $receipts->total(),
                'last_page'    => $receipts->lastPage(),
            ],
        ]);
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $order->store_id !== (int) $store->id) {
            return response()->json(['message' => 'Este pedido no pertenece a tu tienda'], 403);
        }

        $existing = SimpleReceipt::where('store_id', $store->id)
            ->where('order_id', $order->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ya existe un comprobante para este pedido',
                'data'    => $existing,
            ], 409);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Generate receipt number: CV-YYYY-NNNNN
        $year = now()->format('Y');
        $lastNumber = SimpleReceipt::where('store_id', $store->id)
            ->where('receipt_number', 'like', "CV-{$year}-%")
            ->count();
        $receiptNumber = sprintf('CV-%s-%05d', $year, $lastNumber + 1);

        $receipt = SimpleReceipt::create([
            'store_id'       => $store->id,
            'order_id'       => $order->id,
            'receipt_number' => $receiptNumber,
            'receipt_date'   => now()->toDateString(),
            'total'          => $order->total,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Comprobante de venta generado',
            'data'    => $receipt->load('order'),
        ], 201);
    }

    public function show(Request $request, SimpleReceipt $receipt): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store || (int) $receipt->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'data' => $receipt->load('order'),
        ]);
    }

    public function downloadPdf(Request $request, SimpleReceipt $receipt, SimpleReceiptPdfService $pdfService)
    {
        $store = $request->user()->store;

        if (!$store || (int) $receipt->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $receipt->load(['order', 'store']);
        $pdf = $pdfService->generate($receipt);

        return $pdf->download("comprobante-{$receipt->receipt_number}.pdf");
    }
}
