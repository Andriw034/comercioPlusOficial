<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPickingEvent;
use App\Models\OrderPickingSession;
use App\Models\OrderProduct;
use App\Models\ProductCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderPickingController extends Controller
{
    public function show(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $userId = (int) $request->user()->id;
        $session = $this->sessionForOrder($order->id, $userId);
        $order->load(['orderProducts.product']);

        $codesByProductId = ProductCode::query()
            ->where('store_id', (int) $order->store_id)
            ->whereIn('product_id', $order->orderProducts->pluck('product_id')->unique()->values())
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        $lines = $order->orderProducts->map(function (OrderProduct $line) use ($codesByProductId) {
            $product = $line->product;
            $codes = ($codesByProductId[(int) $line->product_id] ?? collect())
                ->map(fn (ProductCode $code) => [
                    'type' => (string) $code->type,
                    'value' => (string) $code->value,
                    'is_primary' => (bool) $code->is_primary,
                ])
                ->values()
                ->all();

            return [
                'order_product_id' => (int) $line->id,
                'product_id' => (int) $line->product_id,
                'product_name' => $product?->name,
                'image_url' => $product?->image_url ?? $product?->image ?? $product?->image_path,
                'quantity' => (int) $line->quantity,
                'qty_picked' => (int) ($line->qty_picked ?? 0),
                'qty_packed' => (int) ($line->qty_packed ?? 0),
                'qty_missing' => (int) ($line->qty_missing ?? 0),
                'pending_qty' => (int) $line->pending_qty,
                'codes' => $codes,
            ];
        })->values();

        return response()->json([
            'message' => 'Picking context',
            'data' => [
                'order' => [
                    'id' => (int) $order->id,
                    'store_id' => (int) $order->store_id,
                    'status' => (string) $order->status,
                    'fulfillment_status' => $this->currentFulfillmentStatus($order),
                    'invoice_number' => $order->invoice_number,
                    'created_at' => $order->created_at?->toISOString(),
                ],
                'lines' => $lines,
            ],
            'meta' => [
                'totals' => $this->totalsFromLines($order->orderProducts),
                'session' => $this->sessionMeta($session),
            ],
        ]);
    }

    public function scan(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'code' => 'required|string|max:128',
            'qty' => 'nullable|integer|min:1|max:999',
        ]);

        $userId = (int) $request->user()->id;
        $codeValue = trim((string) $payload['code']);
        $qty = (int) ($payload['qty'] ?? 1);

        return DB::transaction(function () use ($order, $userId, $codeValue, $qty) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $session = $this->lockedSessionForOrder($lockedOrder->id, $userId);

            if (! $this->canMutatePicking($lockedOrder)) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Picking is not allowed in current state.',
                    fieldMessage: 'The order is not in a scannable state.',
                    errorCode: 'SCAN_INVALID_STATE',
                    scannedCode: $codeValue,
                    incrementFailures: false,
                );
            }

            if ((bool) $session->fallback_required === true) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Scan failed. Manual fallback required.',
                    fieldMessage: 'Fallback required after 3 failed scans',
                    errorCode: 'FALLBACK_REQUIRED',
                    scannedCode: $codeValue,
                    incrementFailures: false,
                );
            }

            $code = ProductCode::query()
                ->where('store_id', (int) $lockedOrder->store_id)
                ->where('value', $codeValue)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->first();

            if (! $code) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Scan failed',
                    fieldMessage: 'Code not found in this store',
                    errorCode: 'CODE_NOT_FOUND',
                    scannedCode: $codeValue,
                );
            }

            $line = OrderProduct::query()
                ->where('order_id', (int) $lockedOrder->id)
                ->where('product_id', (int) $code->product_id)
                ->lockForUpdate()
                ->first();

            if (! $line) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Scan failed',
                    fieldMessage: 'Code exists but product is not part of this order',
                    errorCode: 'CODE_NOT_IN_ORDER',
                    scannedCode: $codeValue,
                    productId: (int) $code->product_id,
                );
            }

            $pendingQty = (int) $line->pending_qty;
            if ($pendingQty <= 0) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Scan failed',
                    fieldMessage: 'This line is already complete',
                    errorCode: 'ITEM_ALREADY_COMPLETE',
                    scannedCode: $codeValue,
                    productId: (int) $line->product_id,
                    orderProductId: (int) $line->id,
                );
            }

            if ($qty > $pendingQty) {
                return $this->scanErrorResponse(
                    order: $lockedOrder,
                    session: $session,
                    message: 'Scan failed',
                    fieldMessage: 'Requested quantity exceeds pending units',
                    errorCode: 'QTY_EXCEEDED',
                    scannedCode: $codeValue,
                    productId: (int) $line->product_id,
                    orderProductId: (int) $line->id,
                );
            }

            $line->qty_picked = (int) ($line->qty_picked ?? 0) + $qty;
            $line->save();

            $this->moveOrderToPicking($lockedOrder);
            $this->resetSessionFailures($session);

            $this->logEvent(
                orderId: (int) $lockedOrder->id,
                userId: $userId,
                mode: OrderPickingEvent::MODE_SCANNER,
                action: 'scan_ok',
                orderProductId: (int) $line->id,
                productId: (int) $line->product_id,
                code: $codeValue,
                qty: $qty,
                errorCode: null,
                message: 'Scan applied',
            );

            return response()->json([
                'message' => 'Scan applied',
                'data' => [
                    'line' => $this->linePayload($line->fresh()),
                    'scan' => [
                        'code' => $codeValue,
                        'qty_applied' => $qty,
                    ],
                ],
                'meta' => [
                    'session' => $this->sessionMeta($session->fresh()),
                ],
            ]);
        });
    }

    public function manual(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'action' => 'required|in:pick_item,pick_by_code,mark_missing,add_note',
            'order_product_id' => 'nullable|integer',
            'code' => 'nullable|string|max:128',
            'qty' => 'nullable|integer|min:1|max:999',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $validationError = $this->validateManualPayload($payload);
        if ($validationError !== null) {
            return $validationError;
        }

        $userId = (int) $request->user()->id;

        return DB::transaction(function () use ($order, $userId, $payload) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $session = $this->lockedSessionForOrder($lockedOrder->id, $userId);

            if (! $this->canMutatePicking($lockedOrder)) {
                return $this->domainError(
                    message: 'Manual action cannot be applied in current order state.',
                    errorCode: 'SCAN_INVALID_STATE',
                    errors: ['order' => ['The order is not in a mutable picking state.']],
                    meta: ['session' => $this->sessionMeta($session)],
                );
            }

            $action = (string) $payload['action'];
            $qty = (int) ($payload['qty'] ?? 1);
            $orderProductId = isset($payload['order_product_id']) ? (int) $payload['order_product_id'] : null;
            $codeValue = isset($payload['code']) ? trim((string) $payload['code']) : null;
            $reason = isset($payload['reason']) ? trim((string) $payload['reason']) : null;
            $note = isset($payload['note']) ? trim((string) $payload['note']) : null;

            $line = null;
            $eventAction = 'manual_note';
            $eventMessage = null;

            if ($action === 'pick_by_code') {
                $code = ProductCode::query()
                    ->where('store_id', (int) $lockedOrder->store_id)
                    ->where('value', (string) $codeValue)
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();

                if (! $code) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'CODE_NOT_FOUND',
                        errors: ['code' => ['Code not found in this store']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                $line = OrderProduct::query()
                    ->where('order_id', (int) $lockedOrder->id)
                    ->where('product_id', (int) $code->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $line) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'CODE_NOT_IN_ORDER',
                        errors: ['code' => ['Code exists but product is not part of this order']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                $action = 'pick_item';
            } else {
                $line = OrderProduct::query()
                    ->where('order_id', (int) $lockedOrder->id)
                    ->where('id', (int) $orderProductId)
                    ->lockForUpdate()
                    ->first();

                if (! $line) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'ORDER_LINE_NOT_FOUND',
                        errors: ['order_product_id' => ['Order line not found in this order']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }
            }

            if ($action === 'pick_item') {
                $pendingQty = (int) $line->pending_qty;
                if ($pendingQty <= 0) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'ITEM_ALREADY_COMPLETE',
                        errors: ['qty' => ['This line is already complete']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                if ($qty > $pendingQty) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'QTY_EXCEEDED',
                        errors: ['qty' => ['Requested quantity exceeds pending units']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                $line->qty_picked = (int) ($line->qty_picked ?? 0) + $qty;
                $line->save();
                $eventAction = 'manual_pick';
                $eventMessage = 'Manual picked units applied';
                $this->moveOrderToPicking($lockedOrder);
            }

            if ($action === 'mark_missing') {
                $pendingQty = (int) $line->pending_qty;
                if ($pendingQty <= 0) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'ITEM_ALREADY_COMPLETE',
                        errors: ['qty' => ['This line is already complete']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                if ($qty > $pendingQty) {
                    return $this->domainError(
                        message: 'Manual action failed',
                        errorCode: 'QTY_EXCEEDED',
                        errors: ['qty' => ['Requested quantity exceeds pending units']],
                        meta: ['session' => $this->sessionMeta($session)],
                    );
                }

                $line->qty_missing = (int) ($line->qty_missing ?? 0) + $qty;
                $line->save();
                $eventAction = 'manual_missing';
                $eventMessage = $reason ?: 'Manual missing units registered';
                $this->moveOrderToPicking($lockedOrder);
            }

            if ($action === 'add_note') {
                $eventAction = 'manual_note';
                $eventMessage = $note;
            }

            $this->resetSessionFailures($session);
            $this->logEvent(
                orderId: (int) $lockedOrder->id,
                userId: $userId,
                mode: OrderPickingEvent::MODE_MANUAL,
                action: $eventAction,
                orderProductId: (int) $line->id,
                productId: (int) $line->product_id,
                code: $codeValue,
                qty: $action === 'add_note' ? 0 : $qty,
                errorCode: null,
                message: $eventMessage,
            );

            return response()->json([
                'message' => 'Manual action applied',
                'data' => [
                    'action' => (string) $payload['action'],
                    'line' => $this->linePayload($line->fresh()),
                ],
                'meta' => [
                    'session' => $this->sessionMeta($session->fresh()),
                ],
            ]);
        });
    }

    public function fallback(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'selected_mode' => 'required|in:manual',
            'reason' => 'nullable|string|max:255',
        ]);

        $userId = (int) $request->user()->id;

        return DB::transaction(function () use ($order, $userId, $payload) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $session = $this->lockedSessionForOrder((int) $lockedOrder->id, $userId);

            $session->scan_consecutive_failures = max(3, (int) $session->scan_consecutive_failures);
            $session->fallback_required = true;
            $session->last_error_code = 'FALLBACK_REQUIRED';
            $session->save();

            $this->logEvent(
                orderId: (int) $lockedOrder->id,
                userId: $userId,
                mode: OrderPickingEvent::MODE_SYSTEM,
                action: 'fallback_triggered',
                orderProductId: null,
                productId: null,
                code: null,
                qty: 0,
                errorCode: null,
                message: (string) ($payload['reason'] ?? 'manual_fallback_selected'),
            );

            return response()->json([
                'message' => 'Fallback mode activated',
                'data' => [
                    'selected_mode' => 'manual',
                ],
                'meta' => [
                    'session' => $this->sessionMeta($session->fresh()),
                ],
            ]);
        });
    }

    public function complete(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'completion_mode' => 'nullable|in:strict',
            'note' => 'nullable|string|max:500',
        ]);

        $userId = (int) $request->user()->id;

        return DB::transaction(function () use ($order, $userId, $payload) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $session = $this->lockedSessionForOrder((int) $lockedOrder->id, $userId);

            $status = $this->currentFulfillmentStatus($lockedOrder);
            if (in_array($status, [Order::FULFILLMENT_DELIVERED, Order::FULFILLMENT_CANCELLED], true)) {
                return $this->domainError(
                    message: 'Picking cannot be completed in current state.',
                    errorCode: 'SCAN_INVALID_STATE',
                    errors: ['order' => ['Order is already closed for fulfillment']],
                    meta: ['session' => $this->sessionMeta($session)],
                );
            }

            $lines = OrderProduct::query()
                ->where('order_id', (int) $lockedOrder->id)
                ->lockForUpdate()
                ->get();

            $incomplete = $lines->contains(function (OrderProduct $line) {
                $resolvedQty = (int) ($line->qty_picked ?? 0) + (int) ($line->qty_missing ?? 0);
                return $resolvedQty !== (int) $line->quantity;
            });

            if ($incomplete) {
                return $this->domainError(
                    message: 'Picking cannot be completed yet',
                    errorCode: 'PICKING_INCOMPLETE',
                    errors: ['order' => ['There are pending quantities']],
                );
            }

            if (in_array($status, [Order::FULFILLMENT_PENDING_PICK, Order::FULFILLMENT_PICKING], true)) {
                $lockedOrder->fulfillment_status = Order::FULFILLMENT_PICKED;
                $lockedOrder->save();
            }

            $this->resetSessionFailures($session);
            $this->logEvent(
                orderId: (int) $lockedOrder->id,
                userId: $userId,
                mode: OrderPickingEvent::MODE_SYSTEM,
                action: 'picking_completed',
                orderProductId: null,
                productId: null,
                code: null,
                qty: 0,
                errorCode: null,
                message: isset($payload['note']) ? (string) $payload['note'] : 'Picking completed',
            );

            return response()->json([
                'message' => 'Picking completed',
                'data' => [
                    'order_id' => (int) $lockedOrder->id,
                    'fulfillment_status' => (string) $lockedOrder->fresh()->fulfillment_status,
                ],
                'meta' => [
                    'totals' => $this->totalsFromLines($lines),
                ],
            ]);
        });
    }

    public function reset(Request $request, Order $order): JsonResponse
    {
        $authError = $this->authorizeMerchantOrder($request, $order);
        if ($authError !== null) {
            return $authError;
        }

        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $userId = (int) $request->user()->id;

        return DB::transaction(function () use ($order, $userId) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $session = $this->lockedSessionForOrder((int) $lockedOrder->id, $userId);

            $status = $this->currentFulfillmentStatus($lockedOrder);
            if (in_array($status, [Order::FULFILLMENT_DELIVERED, Order::FULFILLMENT_CANCELLED], true)) {
                return $this->domainError(
                    message: 'Picking cannot be reset in current state.',
                    errorCode: 'SCAN_INVALID_STATE',
                    errors: ['order' => ['Order is already closed for fulfillment']],
                    meta: ['session' => $this->sessionMeta($session)],
                );
            }

            OrderProduct::query()
                ->where('order_id', (int) $lockedOrder->id)
                ->update([
                    'qty_picked' => 0,
                    'qty_packed' => 0,
                    'qty_missing' => 0,
                ]);

            $lockedOrder->fulfillment_status = Order::FULFILLMENT_PENDING_PICK;
            $lockedOrder->save();

            $this->resetSessionFailures($session);
            $this->logEvent(
                orderId: (int) $lockedOrder->id,
                userId: $userId,
                mode: OrderPickingEvent::MODE_SYSTEM,
                action: 'picking_reset',
                orderProductId: null,
                productId: null,
                code: null,
                qty: 0,
                errorCode: null,
                message: 'Picking reset by merchant',
            );

            return response()->json([
                'message' => 'Picking reset',
                'data' => [
                    'order_id' => (int) $lockedOrder->id,
                    'fulfillment_status' => (string) $lockedOrder->fulfillment_status,
                ],
            ]);
        });
    }

    public function events(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isMerchant()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $store = $user->store()->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $limit = max(1, min(100, (int) $request->integer('limit', 20)));

        $query = OrderPickingEvent::query()
            ->whereHas('order', function ($orderQuery) use ($store) {
                $orderQuery->where('store_id', (int) $store->id);
            })
            ->with([
                'order:id,store_id,status,fulfillment_status',
                'product:id,name',
                'user:id,name',
            ])
            ->orderByDesc('id');

        if ($request->filled('action')) {
            $allowedActions = [
                'scan_ok',
                'scan_error',
                'manual_pick',
                'manual_missing',
                'manual_note',
                'fallback_triggered',
                'picking_completed',
                'picking_reset',
            ];
            $action = (string) $request->string('action');
            if (in_array($action, $allowedActions, true)) {
                $query->where('action', $action);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', (string) $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', (string) $request->string('to'));
        }

        $events = $query->limit($limit)->get();

        return response()->json([
            'message' => 'Eventos de picking',
            'data' => $events->map(function (OrderPickingEvent $event) {
                return [
                    'id' => (int) $event->id,
                    'order_id' => (int) $event->order_id,
                    'order_product_id' => $event->order_product_id ? (int) $event->order_product_id : null,
                    'product_id' => $event->product_id ? (int) $event->product_id : null,
                    'product_name' => $event->product?->name,
                    'user_name' => $event->user?->name,
                    'mode' => (string) $event->mode,
                    'action' => (string) $event->action,
                    'code' => $event->code,
                    'qty' => (int) $event->qty,
                    'error_code' => $event->error_code,
                    'message' => $event->message,
                    'status' => $event->order?->status,
                    'fulfillment_status' => $event->order?->fulfillment_status,
                    'created_at' => $event->created_at?->toIso8601String(),
                ];
            })->values(),
            'meta' => [
                'count' => $events->count(),
                'limit' => $limit,
            ],
        ]);
    }

    private function authorizeMerchantOrder(Request $request, Order $order): ?JsonResponse
    {
        $store = $request->user()->store()->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $order->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }

    private function sessionForOrder(int $orderId, int $userId): OrderPickingSession
    {
        return OrderPickingSession::query()->firstOrCreate(
            [
                'order_id' => $orderId,
                'user_id' => $userId,
            ],
            [
                'scan_consecutive_failures' => 0,
                'fallback_required' => false,
            ],
        );
    }

    private function lockedSessionForOrder(int $orderId, int $userId): OrderPickingSession
    {
        $this->sessionForOrder($orderId, $userId);

        return OrderPickingSession::query()
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function canMutatePicking(Order $order): bool
    {
        return in_array(
            $this->currentFulfillmentStatus($order),
            [Order::FULFILLMENT_PENDING_PICK, Order::FULFILLMENT_PICKING],
            true
        );
    }

    private function currentFulfillmentStatus(Order $order): string
    {
        return (string) ($order->fulfillment_status ?: Order::FULFILLMENT_PENDING_PICK);
    }

    private function moveOrderToPicking(Order $order): void
    {
        if ($this->currentFulfillmentStatus($order) === Order::FULFILLMENT_PENDING_PICK) {
            $order->fulfillment_status = Order::FULFILLMENT_PICKING;
            $order->save();
        }
    }

    private function resetSessionFailures(OrderPickingSession $session): void
    {
        $session->scan_consecutive_failures = 0;
        $session->fallback_required = false;
        $session->last_error_code = null;
        $session->last_code = null;
        $session->save();
    }

    private function scanErrorResponse(
        Order $order,
        OrderPickingSession $session,
        string $message,
        string $fieldMessage,
        string $errorCode,
        ?string $scannedCode = null,
        bool $incrementFailures = true,
        ?int $orderProductId = null,
        ?int $productId = null
    ): JsonResponse {
        $failures = (int) $session->scan_consecutive_failures;
        $fallbackRequired = (bool) $session->fallback_required;

        if ($incrementFailures) {
            $failures++;
            if ($failures >= 3) {
                $failures = 3;
                $fallbackRequired = true;
                $errorCode = 'FALLBACK_REQUIRED';
                $message = 'Scan failed. Manual fallback required.';
                $fieldMessage = 'Fallback required after 3 failed scans';
            }
        }

        $session->scan_consecutive_failures = $failures;
        $session->fallback_required = $fallbackRequired;
        $session->last_error_code = $errorCode;
        $session->last_code = $scannedCode;
        $session->save();

        $this->logEvent(
            orderId: (int) $order->id,
            userId: (int) ($session->user_id ?? 0),
            mode: OrderPickingEvent::MODE_SCANNER,
            action: 'scan_error',
            orderProductId: $orderProductId,
            productId: $productId,
            code: $scannedCode,
            qty: 0,
            errorCode: $errorCode,
            message: $fieldMessage,
        );

        if ($incrementFailures && $fallbackRequired && $failures === 3) {
            $this->logEvent(
                orderId: (int) $order->id,
                userId: (int) ($session->user_id ?? 0),
                mode: OrderPickingEvent::MODE_SYSTEM,
                action: 'fallback_triggered',
                orderProductId: null,
                productId: null,
                code: $scannedCode,
                qty: 0,
                errorCode: 'FALLBACK_REQUIRED',
                message: 'Fallback required after 3 consecutive scan errors',
            );
        }

        return $this->domainError(
            message: $message,
            errorCode: $errorCode,
            errors: ['code' => [$fieldMessage]],
            meta: ['session' => $this->sessionMeta($session)],
        );
    }

    private function validateManualPayload(array $payload): ?JsonResponse
    {
        $action = (string) ($payload['action'] ?? '');
        $needsLine = in_array($action, ['pick_item', 'mark_missing', 'add_note'], true);

        if ($needsLine && empty($payload['order_product_id'])) {
            return $this->domainError(
                message: 'The given data was invalid.',
                errorCode: 'VALIDATION_ERROR',
                errors: ['order_product_id' => ['order_product_id is required for this action']],
            );
        }

        if (in_array($action, ['pick_item', 'mark_missing'], true) && empty($payload['qty'])) {
            return $this->domainError(
                message: 'The given data was invalid.',
                errorCode: 'VALIDATION_ERROR',
                errors: ['qty' => ['qty is required for this action']],
            );
        }

        if ($action === 'pick_by_code' && empty($payload['code'])) {
            return $this->domainError(
                message: 'The given data was invalid.',
                errorCode: 'VALIDATION_ERROR',
                errors: ['code' => ['code is required for this action']],
            );
        }

        if ($action === 'mark_missing' && empty($payload['reason'])) {
            return $this->domainError(
                message: 'The given data was invalid.',
                errorCode: 'VALIDATION_ERROR',
                errors: ['reason' => ['reason is required for this action']],
            );
        }

        if ($action === 'add_note' && empty($payload['note'])) {
            return $this->domainError(
                message: 'The given data was invalid.',
                errorCode: 'VALIDATION_ERROR',
                errors: ['note' => ['note is required for this action']],
            );
        }

        return null;
    }

    private function linePayload(OrderProduct $line): array
    {
        return [
            'order_product_id' => (int) $line->id,
            'product_id' => (int) $line->product_id,
            'quantity' => (int) $line->quantity,
            'qty_picked' => (int) ($line->qty_picked ?? 0),
            'qty_missing' => (int) ($line->qty_missing ?? 0),
            'pending_qty' => (int) $line->pending_qty,
        ];
    }

    private function sessionMeta(OrderPickingSession $session): array
    {
        return [
            'scan_consecutive_failures' => (int) $session->scan_consecutive_failures,
            'fallback_required' => (bool) $session->fallback_required,
        ];
    }

    private function totalsFromLines(Collection $lines): array
    {
        $orderedUnits = (int) $lines->sum(fn (OrderProduct $line) => (int) $line->quantity);
        $pickedUnits = (int) $lines->sum(fn (OrderProduct $line) => (int) ($line->qty_picked ?? 0));
        $missingUnits = (int) $lines->sum(fn (OrderProduct $line) => (int) ($line->qty_missing ?? 0));
        $pendingUnits = (int) $lines->sum(fn (OrderProduct $line) => (int) $line->pending_qty);

        $completionPct = $orderedUnits > 0
            ? round((($pickedUnits + $missingUnits) / $orderedUnits) * 100, 2)
            : 0.0;

        return [
            'ordered_units' => $orderedUnits,
            'picked_units' => $pickedUnits,
            'missing_units' => $missingUnits,
            'pending_units' => $pendingUnits,
            'completion_pct' => $completionPct,
        ];
    }

    private function logEvent(
        int $orderId,
        int $userId,
        string $mode,
        string $action,
        ?int $orderProductId,
        ?int $productId,
        ?string $code,
        int $qty,
        ?string $errorCode,
        ?string $message
    ): void {
        OrderPickingEvent::query()->create([
            'order_id' => $orderId,
            'order_product_id' => $orderProductId,
            'product_id' => $productId,
            'user_id' => $userId > 0 ? $userId : null,
            'mode' => $mode,
            'action' => $action,
            'code' => $code,
            'qty' => $qty,
            'error_code' => $errorCode,
            'message' => $message,
        ]);
    }

    private function domainError(string $message, string $errorCode, array $errors, array $meta = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'error_code' => $errorCode,
            'meta' => (object) $meta,
        ], 422);
    }
}
