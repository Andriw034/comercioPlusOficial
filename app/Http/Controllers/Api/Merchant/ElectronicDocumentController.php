<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\ElectronicDocument;
use App\Models\Order;
use App\Services\ElectronicInvoicingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElectronicDocumentController extends Controller
{
    public function __construct(
        private ElectronicInvoicingService $invoicingService
    ) {}

    /**
     * GET /merchant/invoicing
     * Listar documentos electrónicos de la tienda.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $query = ElectronicDocument::where('store_id', $store->id)
            ->with(['items', 'taxes']);

        // Filtros opcionales
        if ($request->filled('status')) {
            $query->where('dian_status', $request->input('status'));
        }
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }
        if ($request->filled('customer')) {
            $search = $request->input('customer');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_identification', 'like', "%{$search}%");
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $docs = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'message' => 'Documentos electrónicos',
            'data'    => $docs->items(),
            'meta'    => [
                'current_page' => $docs->currentPage(),
                'last_page'    => $docs->lastPage(),
                'per_page'     => $docs->perPage(),
                'total'        => $docs->total(),
            ],
        ]);
    }

    /**
     * GET /merchant/invoicing/{document}
     * Detalle de un documento electrónico.
     */
    public function show(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        $document->load(['items', 'taxes', 'logs', 'referenceDocument', 'referencedBy']);

        return response()->json([
            'message' => 'Detalle del documento',
            'data'    => $document,
        ]);
    }

    /**
     * POST /merchant/invoicing
     * Crear factura electrónica nueva.
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $validated = $request->validate([
            'order_id'                     => 'nullable|exists:orders,id',
            'issuer_nit'                   => 'required|string|max:20',
            'issuer_name'                  => 'nullable|string|max:255',
            'issuer_email'                 => 'nullable|email|max:255',
            'issuer_phone'                 => 'nullable|string|max:30',
            'issuer_address'               => 'nullable|string|max:500',
            'issuer_city'                  => 'nullable|string|max:100',
            'issuer_department'            => 'nullable|string|max:100',
            'customer_identification_type' => 'required|string|in:CC,NIT,CE,PP,TI',
            'customer_identification'      => 'required|string|max:30',
            'customer_name'                => 'required|string|max:255',
            'customer_email'               => 'nullable|email|max:255',
            'customer_phone'               => 'nullable|string|max:30',
            'customer_address'             => 'nullable|string|max:500',
            'customer_city'                => 'nullable|string|max:100',
            'customer_department'          => 'nullable|string|max:100',
            'payment_method'               => 'nullable|string|in:contado,credito',
            'payment_means'                => 'nullable|string|in:efectivo,transferencia,tarjeta',
            'payment_due_date'             => 'nullable|date|after_or_equal:today',
            'notes'                        => 'nullable|string|max:1000',
            'items'                        => 'required|array|min:1',
            'items.*.product_id'           => 'nullable|exists:products,id',
            'items.*.description'          => 'required|string|max:255',
            'items.*.code'                 => 'nullable|string|max:50',
            'items.*.unit_measure'         => 'nullable|string|max:10',
            'items.*.quantity'             => 'required|numeric|min:0.001',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.discount'             => 'nullable|numeric|min:0',
            'items.*.tax_type'             => 'nullable|string|in:IVA,INC,ICA',
            'items.*.tax_rate'             => 'nullable|numeric|min:0|max:100',
        ]);

        $doc = $this->invoicingService->createInvoice($store, $validated, $request->user()->id);

        return response()->json([
            'message' => 'Factura creada exitosamente',
            'data'    => $doc,
        ], 201);
    }

    /**
     * POST /merchant/invoicing/from-order/{order}
     * Crear factura desde una orden existente.
     */
    public function createFromOrder(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $order->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'issuer_nit'                   => 'required|string|max:20',
            'issuer_name'                  => 'nullable|string|max:255',
            'issuer_email'                 => 'nullable|email|max:255',
            'issuer_phone'                 => 'nullable|string|max:30',
            'issuer_address'               => 'nullable|string|max:500',
            'issuer_city'                  => 'nullable|string|max:100',
            'issuer_department'            => 'nullable|string|max:100',
            'customer_identification_type' => 'required|string|in:CC,NIT,CE,PP,TI',
            'customer_identification'      => 'required|string|max:30',
            'customer_name'                => 'required|string|max:255',
            'customer_email'               => 'nullable|email|max:255',
            'customer_phone'               => 'nullable|string|max:30',
            'customer_address'             => 'nullable|string|max:500',
            'customer_city'                => 'nullable|string|max:100',
            'customer_department'          => 'nullable|string|max:100',
        ]);

        $issuerData = array_filter($validated, fn ($k) => str_starts_with($k, 'issuer_'), ARRAY_FILTER_USE_KEY);
        $customerData = array_filter($validated, fn ($k) => str_starts_with($k, 'customer_'), ARRAY_FILTER_USE_KEY);

        $doc = $this->invoicingService->createFromOrder($store, $order, $issuerData, $customerData, $request->user()->id);

        return response()->json([
            'message' => 'Factura creada desde orden #' . $order->id,
            'data'    => $doc,
        ], 201);
    }

    /**
     * PUT /merchant/invoicing/{document}
     * Actualizar documento en draft/pending.
     */
    public function update(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        $validated = $request->validate([
            'customer_identification_type' => 'nullable|string|in:CC,NIT,CE,PP,TI',
            'customer_identification'      => 'nullable|string|max:30',
            'customer_name'                => 'nullable|string|max:255',
            'customer_email'               => 'nullable|email|max:255',
            'customer_phone'               => 'nullable|string|max:30',
            'customer_address'             => 'nullable|string|max:500',
            'customer_city'                => 'nullable|string|max:100',
            'customer_department'          => 'nullable|string|max:100',
            'payment_method'               => 'nullable|string|in:contado,credito',
            'payment_means'                => 'nullable|string|in:efectivo,transferencia,tarjeta',
            'payment_due_date'             => 'nullable|date',
            'notes'                        => 'nullable|string|max:1000',
            'items'                        => 'nullable|array|min:1',
            'items.*.product_id'           => 'nullable|exists:products,id',
            'items.*.description'          => 'required_with:items|string|max:255',
            'items.*.code'                 => 'nullable|string|max:50',
            'items.*.unit_measure'         => 'nullable|string|max:10',
            'items.*.quantity'             => 'required_with:items|numeric|min:0.001',
            'items.*.unit_price'           => 'required_with:items|numeric|min:0',
            'items.*.discount'             => 'nullable|numeric|min:0',
            'items.*.tax_type'             => 'nullable|string|in:IVA,INC,ICA',
            'items.*.tax_rate'             => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $doc = $this->invoicingService->update($document, $validated, $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Documento actualizado',
            'data'    => $doc,
        ]);
    }

    /**
     * POST /merchant/invoicing/{document}/send
     * Generate XML + CUFE, send to DIAN via Matias API.
     */
    public function send(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        try {
            $result = $this->invoicingService->sendToDian($document, $request->user()->id);

            if ($result['success']) {
                return response()->json([
                    'message'  => 'Documento enviado a la DIAN correctamente',
                    'data'     => $document->fresh(['items', 'taxes', 'logs']),
                    'track_id' => $result['track_id'],
                    'cufe'     => $result['cufe'],
                ]);
            }

            return response()->json([
                'message' => 'Error al enviar a la DIAN: ' . $result['message'],
                'data'    => $document->fresh(['items', 'taxes', 'logs']),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /merchant/invoicing/{document}/status
     * Check DIAN status via Matias API and update locally.
     */
    public function checkStatus(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        $result = $this->invoicingService->checkDianStatus($document, $request->user()->id);

        return response()->json([
            'message' => $result['message'] ?? 'Consulta realizada',
            'data'    => $document->fresh(['items', 'taxes', 'logs']),
            'status'  => $result['status'] ?? $document->dian_status,
        ], $result['success'] ? 200 : 422);
    }

    /**
     * POST /merchant/invoicing/{document}/cancel
     * Anular documento aprobado (dentro del plazo).
     */
    public function cancel(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        if (!$document->canBeCancelled()) {
            return response()->json([
                'message' => 'Este documento no puede ser anulado. Debe estar aprobado y dentro de los '
                    . config('invoicing.rules.cancel_max_days') . ' días permitidos.',
            ], 422);
        }

        $reason = $request->input('reason', 'Anulación solicitada por el comerciante.');

        try {
            $doc = $this->invoicingService->changeStatus(
                $document,
                ElectronicDocument::STATUS_CANCELLED,
                $reason,
                null,
                $request->user()->id
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Documento anulado',
            'data'    => $doc,
        ]);
    }

    /**
     * POST /merchant/invoicing/{document}/credit-note
     * Crear nota crédito desde una factura aprobada.
     */
    public function creditNote(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'items'  => 'nullable|array|min:1',
            'items.*.product_id'  => 'nullable|exists:products,id',
            'items.*.description' => 'required_with:items|string|max:255',
            'items.*.code'        => 'nullable|string|max:50',
            'items.*.quantity'    => 'required_with:items|numeric|min:0.001',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
            'items.*.tax_type'    => 'nullable|string|in:IVA,INC,ICA',
            'items.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $doc = $this->invoicingService->createCreditNote(
                $document,
                $validated['reason'],
                $validated['items'] ?? [],
                $request->user()->id
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Nota crédito creada',
            'data'    => $doc,
        ], 201);
    }

    /**
     * GET /merchant/invoicing/{document}/logs
     * Historial de auditoría del documento.
     */
    public function logs(Request $request, ElectronicDocument $document): JsonResponse
    {
        $authError = $this->authorizeDocument($request, $document);
        if ($authError) return $authError;

        $logs = $document->logs()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Historial del documento',
            'data'    => $logs,
        ]);
    }

    /**
     * GET /merchant/invoicing/stats
     * Resumen de facturación de la tienda.
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $baseQuery = ElectronicDocument::where('store_id', $store->id);

        $total = (clone $baseQuery)->count();
        $byStatus = (clone $baseQuery)
            ->selectRaw('dian_status, COUNT(*) as count')
            ->groupBy('dian_status')
            ->pluck('count', 'dian_status');
        $totalApproved = (clone $baseQuery)
            ->approved()
            ->sum('total');

        return response()->json([
            'message' => 'Estadísticas de facturación',
            'data'    => [
                'total_documents'       => $total,
                'by_status'             => $byStatus,
                'total_invoiced_amount' => round($totalApproved, 2),
                'currency'              => config('invoicing.defaults.currency'),
            ],
        ]);
    }

    private function authorizeDocument(Request $request, ElectronicDocument $document): ?JsonResponse
    {
        $store = $request->user()->store()->first();
        if (!$store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }
        if ((int) $document->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        return null;
    }
}
