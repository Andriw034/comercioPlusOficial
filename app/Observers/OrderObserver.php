<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    private const PAID_STATUSES = ['paid', 'approved', 'completed'];
    private const CANCELLED_STATUSES = ['cancelled', 'refunded'];

    public function __construct(private readonly InventoryService $inventoryService) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $from = (string) $order->getOriginal('status');
        $to = (string) $order->status;

        try {
            $this->handleStatusTransition($order, $from, $to);
        } catch (\Throwable $e) {
            Log::error('OrderObserver inventory sync failed', [
                'order_id' => $order->id,
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleStatusTransition(Order $order, string $from, string $to): void
    {
        $becamePaid = ! in_array($from, self::PAID_STATUSES, true) && in_array($to, self::PAID_STATUSES, true);
        $becameCancelled = ! in_array($from, self::CANCELLED_STATUSES, true) && in_array($to, self::CANCELLED_STATUSES, true);
        $wasRefunded = $from !== 'refunded' && $to === 'refunded';

        $items = $this->buildItemsArray($order);

        if ($becamePaid) {
            $this->inventoryService->recordSale($order, (int) ($order->user_id ?? 1));
        }

        if ($becameCancelled) {
            $this->inventoryService->revertForOrder(
                items: $items,
                orderId: (int) $order->id,
                storeId: (int) $order->store_id,
                actorId: (int) ($order->user_id ?? 1),
                reason: 'cancel',
            );
        }

        if ($wasRefunded) {
            $this->inventoryService->revertForOrder(
                items: $items,
                orderId: (int) $order->id,
                storeId: (int) $order->store_id,
                actorId: (int) ($order->user_id ?? 1),
                reason: 'return',
            );
        }
    }

    private function buildItemsArray(Order $order): array
    {
        $order->loadMissing('ordenproducts');

        return $order->ordenproducts->map(fn ($op) => [
            'product_id' => (int) $op->product_id,
            'quantity' => (int) $op->quantity,
            'unit_price' => (float) ($op->unit_price ?? 0),
        ])->toArray();
    }
}
