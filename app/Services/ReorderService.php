<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReorderService
{
    public function getSuggestions(int $storeId, ?int $threshold = null): Collection
    {
        $query = Product::where('store_id', $storeId)->with('category');
        if ($threshold !== null) {
            $query->where('stock', '<=', $threshold);
        } else {
            $query->whereColumn('stock', '<=', 'reorder_point');
        }

        return $query->orderBy('stock')->get()->map(function (Product $product) {
            $suggested = $this->computeSuggestedQty($product);

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'current_stock' => (int) $product->stock,
                'reorder_point' => (int) $product->reorder_point,
                'suggested_qty' => $suggested,
                'last_cost' => (float) ($product->cost_price ?? 0),
                'estimated_cost' => round(((float) ($product->cost_price ?? 0)) * $suggested, 2),
            ];
        });
    }

    public function createRequest(int $storeId, int $createdBy, array $items): PurchaseRequest
    {
        return DB::transaction(function () use ($storeId, $createdBy, $items) {
            $purchaseRequest = PurchaseRequest::create([
                'store_id' => $storeId,
                'created_by' => $createdBy,
                'status' => PurchaseRequest::STATUS_DRAFT,
                'period_tag' => Carbon::now()->format('o-\WW'),
            ]);

            foreach ($items as $item) {
                $product = Product::where('store_id', $storeId)->findOrFail($item['product_id']);
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_id' => $product->id,
                    'current_stock' => (int) $product->stock,
                    'suggested_qty' => (int) ($item['suggested_qty'] ?? 0),
                    'ordered_qty' => (int) ($item['ordered_qty'] ?? $item['suggested_qty'] ?? 0),
                    'last_cost' => (float) ($item['last_cost'] ?? $product->cost_price ?? 0),
                ]);
            }

            return $purchaseRequest->load('items.product');
        });
    }

    private function computeSuggestedQty(Product $product): int
    {
        $avg30 = $this->avgDailySales((int) $product->id, 30);
        if ($avg30 > 0) {
            $needed = (int) ceil($avg30 * 14) - (int) $product->stock;
        } else {
            $needed = ((int) $product->reorder_point * 3) - (int) $product->stock;
        }

        return max(1, $needed);
    }

    private function avgDailySales(int $productId, int $days): float
    {
        $from = Carbon::now()->subDays($days)->startOfDay();
        $totalSold = InventoryMovement::where('product_id', $productId)
            ->where('type', InventoryMovement::TYPE_SALE)
            ->where('created_at', '>=', $from)
            ->sum(DB::raw('ABS(quantity)'));

        return $days > 0 ? round($totalSold / $days, 4) : 0.0;
    }
}
