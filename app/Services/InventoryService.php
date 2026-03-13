<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function recordSale(Order $order, ?int $actorId = null): void
    {
        $order->loadMissing('ordenproducts');
        $items = $order->ordenproducts->map(fn ($line) => [
            'product_id' => (int) $line->product_id,
            'quantity' => (int) $line->quantity,
            'unit_price' => (float) ($line->unit_price ?? 0),
        ])->values()->all();

        $this->decrementForOrder(
            items: $items,
            orderId: (int) $order->id,
            storeId: (int) $order->store_id,
            actorId: (int) ($actorId ?? $order->user_id ?? auth()->id() ?? 1),
        );
    }

    public function decrementForOrder(array $items, int $orderId, int $storeId, int $actorId): void
    {
        $alreadyRecorded = InventoryMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $orderId)
            ->where('type', InventoryMovement::TYPE_SALE)
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        DB::transaction(function () use ($items, $orderId, $storeId, $actorId) {
            $alreadyRecordedInTransaction = InventoryMovement::query()
                ->where('reference_type', 'order')
                ->where('reference_id', $orderId)
                ->where('type', InventoryMovement::TYPE_SALE)
                ->exists();

            if ($alreadyRecordedInTransaction) {
                return;
            }

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if ((int) $product->store_id !== $storeId) {
                    throw new RuntimeException('Producto no pertenece a la tienda.');
                }

                $qty = abs((int) $item['quantity']);
                if ($product->stock < $qty) {
                    throw new RuntimeException("Stock insuficiente para {$product->name}.");
                }

                $stockAfter = $product->stock - $qty;
                $product->decrement('stock', $qty);

                $this->record(
                    $storeId,
                    (int) $product->id,
                    InventoryMovement::TYPE_SALE,
                    -$qty,
                    $stockAfter,
                    (float) ($product->cost_price ?? 0),
                    (float) ($item['unit_price'] ?? $product->price),
                    'order',
                    $orderId,
                    "Venta automatica. Pedido #{$orderId}",
                    $actorId
                );
            }
        });
    }

    public function revertForOrder(array $items, int $orderId, int $storeId, int $actorId, string $reason = 'cancel'): void
    {
        $type = $reason === 'return' ? InventoryMovement::TYPE_RETURN : InventoryMovement::TYPE_CANCEL;
        $label = $type === InventoryMovement::TYPE_RETURN ? 'Devolucion' : 'Cancelacion';

        DB::transaction(function () use ($items, $orderId, $storeId, $actorId, $type, $label) {
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if ((int) $product->store_id !== $storeId) {
                    throw new RuntimeException('Producto no pertenece a la tienda.');
                }

                $qty = abs((int) $item['quantity']);
                $stockAfter = $product->stock + $qty;
                $product->increment('stock', $qty);

                $this->record(
                    $storeId,
                    (int) $product->id,
                    $type,
                    $qty,
                    $stockAfter,
                    (float) ($product->cost_price ?? 0),
                    (float) ($item['unit_price'] ?? $product->price),
                    'order',
                    $orderId,
                    "{$label} de pedido #{$orderId}",
                    $actorId
                );
            }
        });
    }

    public function incrementForPurchase(int $purchaseRequestId, int $storeId, int $actorId): void
    {
        $purchaseRequest = PurchaseRequest::with('items.product')->findOrFail($purchaseRequestId);

        DB::transaction(function () use ($purchaseRequest, $storeId, $actorId) {
            foreach ($purchaseRequest->items as $item) {
                if ((int) $item->ordered_qty <= 0) {
                    continue;
                }

                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                if ((int) $product->store_id !== $storeId) {
                    throw new RuntimeException('Producto no pertenece a la tienda.');
                }

                $qty = (int) $item->ordered_qty;
                $stockAfter = $product->stock + $qty;
                $product->increment('stock', $qty);

                $this->record(
                    $storeId,
                    (int) $product->id,
                    InventoryMovement::TYPE_PURCHASE,
                    $qty,
                    $stockAfter,
                    (float) ($item->last_cost ?? $product->cost_price ?? 0),
                    null,
                    'purchase_request',
                    (int) $purchaseRequest->id,
                    "Reposicion recibida. Solicitud #{$purchaseRequest->id}",
                    $actorId
                );
            }
        });
    }

    public function adjust(int $productId, int $newStock, string $note, int $storeId, int $actorId): InventoryMovement
    {
        return DB::transaction(function () use ($productId, $newStock, $note, $storeId, $actorId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            if ((int) $product->store_id !== $storeId) {
                throw new RuntimeException('Producto no pertenece a la tienda.');
            }

            $delta = $newStock - (int) $product->stock;
            return $this->recordAdjustment($product, $delta, $note, $storeId, $actorId);
        });
    }

    public function recordAdjustment(Product $product, int $delta, string $reason, int $storeId, int $actorId): InventoryMovement
    {
        if ((int) $product->store_id !== $storeId) {
            throw new RuntimeException('Producto no pertenece a la tienda.');
        }

        $currentStock = (int) $product->stock;
        $newStock = $currentStock + $delta;
        if ($newStock < 0) {
            throw new RuntimeException('El ajuste deja el stock en negativo.');
        }

        $product->update(['stock' => $newStock]);

        return $this->record(
            $storeId,
            (int) $product->id,
            InventoryMovement::TYPE_ADJUSTMENT,
            $delta,
            $newStock,
            (float) ($product->cost_price ?? 0),
            (float) $product->price,
            'manual',
            null,
            $reason,
            $actorId
        );
    }

    public function getLowStockProducts(int $storeId, ?int $threshold = null): Collection
    {
        $query = Product::where('store_id', $storeId);
        if ($threshold !== null) {
            $query->where('stock', '<=', $threshold);
        } else {
            $query->whereColumn('stock', '<=', 'reorder_point');
        }

        return $query->orderBy('stock')->get();
    }

    private function record(
        int $storeId,
        int $productId,
        string $type,
        int $quantity,
        int $stockAfter,
        ?float $unitCost,
        ?float $unitPrice,
        ?string $referenceType,
        ?int $referenceId,
        ?string $note,
        int $createdBy
    ): InventoryMovement {
        return InventoryMovement::create([
            'store_id' => $storeId,
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_after' => $stockAfter,
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }
}
