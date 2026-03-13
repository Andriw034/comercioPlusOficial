<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePurchaseRequestRequest;
use App\Http\Requests\UpdatePurchaseRequestRequest;
use App\Models\PurchaseRequest;
use App\Models\Store;
use App\Services\InventoryService;
use App\Services\ReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function __construct(
        private readonly ReorderService $reorderService,
        private readonly InventoryService $inventoryService
    ) {}

    public function suggestions(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $threshold = $request->integer('threshold') ?: null;
        $suggestions = $this->reorderService->getSuggestions((int) $store->id, $threshold);

        return response()->json([
            'data' => $suggestions,
            'total' => $suggestions->count(),
        ]);
    }

    public function index(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $query = PurchaseRequest::forStore((int) $store->id)
            ->with(['items.product:id,name,stock', 'creator:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        $requests = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $requests->map(fn ($r) => $this->formatRequest($r)),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(CreatePurchaseRequestRequest $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);
        $purchaseRequest = $this->reorderService->createRequest(
            storeId: (int) $store->id,
            createdBy: (int) auth()->id(),
            items: $request->validated('items')
        );

        return response()->json([
            'message' => 'Solicitud de reposicion creada.',
            'data' => $this->formatRequest($purchaseRequest->load('items.product')),
        ], 201);
    }

    public function show(Store $store, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorizeStore($store);
        $this->authorizePurchaseRequest($purchaseRequest, $store);

        return response()->json([
            'data' => $this->formatRequest($purchaseRequest->load('items.product')),
        ]);
    }

    public function update(
        UpdatePurchaseRequestRequest $request,
        Store $store,
        PurchaseRequest $purchaseRequest
    ): JsonResponse {
        $this->authorizeStore($store);
        $this->authorizePurchaseRequest($purchaseRequest, $store);

        $validated = $request->validated();
        $newStatus = $validated['status'] ?? null;

        if ($newStatus && $newStatus !== $purchaseRequest->status) {
            $this->validateTransition((string) $purchaseRequest->status, (string) $newStatus);

            if ($newStatus === PurchaseRequest::STATUS_RECEIVED) {
                $this->inventoryService->incrementForPurchase(
                    purchaseRequestId: (int) $purchaseRequest->id,
                    storeId: (int) $store->id,
                    actorId: (int) auth()->id(),
                );

                $purchaseRequest->update([
                    'status' => $newStatus,
                    'received_at' => now(),
                ]);
            } else {
                $purchaseRequest->update(['status' => $newStatus]);
            }
        }

        if ($purchaseRequest->isDraft()) {
            if (array_key_exists('notes', $validated)) {
                $purchaseRequest->update(['notes' => $validated['notes']]);
            }
            if (array_key_exists('expected_date', $validated)) {
                $purchaseRequest->update(['expected_date' => $validated['expected_date']]);
            }
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $purchaseRequest->items()
                        ->where('product_id', $item['product_id'])
                        ->update(['ordered_qty' => $item['ordered_qty']]);
                }
            }
        }

        return response()->json([
            'message' => 'Solicitud actualizada correctamente.',
            'data' => $this->formatRequest($purchaseRequest->fresh()->load('items.product')),
        ]);
    }

    private function formatRequest(PurchaseRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'status_label' => $request->status_label,
            'period_tag' => $request->period_tag,
            'notes' => $request->notes,
            'expected_date' => $request->expected_date?->toDateString(),
            'received_at' => $request->received_at?->toIso8601String(),
            'total_estimado' => $request->total_estimated,
            'items' => $request->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'current_stock' => $item->current_stock,
                'suggested_qty' => $item->suggested_qty,
                'ordered_qty' => $item->ordered_qty,
                'last_cost' => (float) ($item->last_cost ?? 0),
                'estimated_cost' => $item->estimated_cost,
            ]),
            'created_by' => $request->creator?->name,
            'created_at' => $request->created_at?->toIso8601String(),
        ];
    }

    private function authorizeStore(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para acceder a esta tienda.');
        }
    }

    private function authorizePurchaseRequest(PurchaseRequest $purchaseRequest, Store $store): void
    {
        if ((int) $purchaseRequest->store_id !== (int) $store->id) {
            abort(404);
        }
    }

    private function validateTransition(string $current, string $next): void
    {
        $allowed = [
            'draft' => ['sent', 'cancelled'],
            'sent' => ['received', 'cancelled'],
            'received' => [],
            'cancelled' => [],
        ];

        if (! in_array($next, $allowed[$current] ?? [], true)) {
            abort(422, "No se puede cambiar de '{$current}' a '{$next}'.");
        }
    }
}
