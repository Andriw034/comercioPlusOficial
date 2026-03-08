<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDecisionsController extends Controller
{
    public function decisions(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $storeId = (int) $store->id;

        $rows = DB::select("
            SELECT
                p.id,
                p.name,
                p.sku,
                p.stock,
                COALESCE(p.reorder_point, 5)  AS reorder_point,
                COALESCE(p.price, 0)          AS price,
                COALESCE(v30.units, 0)        AS sold_30d,
                COALESCE(v7.units,  0)        AS sold_7d,
                COALESCE(v30.units, 0) / 30.0 AS daily_rotation,
                ps.supplier_name,
                ps.supplier_phone,
                ps.purchase_price             AS supplier_price,
                ps.delivery_days              AS supplier_delivery_days
            FROM products p

            LEFT JOIN (
                SELECT op.product_id, SUM(op.quantity) AS units
                FROM order_products op
                JOIN orders o ON o.id = op.order_id
                WHERE o.created_at >= NOW() - INTERVAL 30 DAY
                  AND o.status NOT IN ('cancelled')
                GROUP BY op.product_id
            ) v30 ON v30.product_id = p.id

            LEFT JOIN (
                SELECT op.product_id, SUM(op.quantity) AS units
                FROM order_products op
                JOIN orders o ON o.id = op.order_id
                WHERE o.created_at >= NOW() - INTERVAL 7 DAY
                  AND o.status NOT IN ('cancelled')
                GROUP BY op.product_id
            ) v7 ON v7.product_id = p.id

            LEFT JOIN product_suppliers ps ON ps.product_id = p.id AND ps.is_primary = 1

            WHERE p.store_id = ?
              AND p.stock <= GREATEST(COALESCE(p.reorder_point, 5), 1) * 2

            ORDER BY (p.stock - COALESCE(p.reorder_point, 5)) ASC
        ", [$storeId]);

        $productIds = array_column($rows, 'id');

        $allSuppliers = collect();
        if (!empty($productIds)) {
            $allSuppliers = DB::table('product_suppliers')
                ->whereIn('product_id', $productIds)
                ->orderByDesc('is_primary')
                ->get()
                ->groupBy('product_id');
        }

        $criticalCount = 0;
        $highCount     = 0;
        $mediumCount   = 0;
        $lowCount      = 0;
        $totalRestockValue = 0.0;

        $data = array_map(function (object $row) use (
            &$criticalCount, &$highCount, &$mediumCount, &$lowCount, &$totalRestockValue,
            $allSuppliers
        ): array {
            $stock        = (int)   $row->stock;
            $reorderPoint = (int)   $row->reorder_point;
            $sold30d      = (float) $row->sold_30d;
            $sold7d       = (int)   $row->sold_7d;
            $dailyRot     = (float) $row->daily_rotation;
            $price        = (float) $row->price;

            // Priority
            if ($stock === 0) {
                $priority = 'critical';
                $criticalCount++;
            } elseif ($stock <= $reorderPoint) {
                $priority = 'high';
                $highCount++;
            } elseif ($stock <= (int) ($reorderPoint * 1.5) && $sold30d > 5) {
                $priority = 'medium';
                $mediumCount++;
            } else {
                $priority = 'low';
                $lowCount++;
            }

            // Suggested qty: MAX(reorder_point*2 - stock, ceil(daily_rotation*14))
            $fromReorder  = max(0, $reorderPoint * 2 - $stock);
            $fromRotation = $dailyRot > 0 ? (int) ceil($dailyRot * 14) : 0;
            $suggestedQty = max($fromReorder, $fromRotation, 1);

            // Days of stock
            $daysOfStock = $dailyRot > 0 ? round($stock / $dailyRot, 1) : null;

            // Projected stockout date
            $projectedStockout = null;
            if ($daysOfStock !== null) {
                $projectedStockout = Carbon::now()->addDays((int) $daysOfStock)->format('Y-m-d');
            }

            $totalRestockValue += $suggestedQty * $price;

            $supplierRows = $allSuppliers[$row->id] ?? collect();
            $suppliers = $supplierRows->map(fn($s) => [
                'name'           => $s->supplier_name,
                'phone'          => $s->supplier_phone,
                'purchase_price' => (float) $s->purchase_price,
                'delivery_days'  => (int) $s->delivery_days,
                'is_primary'     => (bool) $s->is_primary,
            ])->values()->toArray();

            return [
                'id'                => (int)    $row->id,
                'name'              => (string) $row->name,
                'sku'               => $row->sku,
                'stock'             => $stock,
                'reorder_point'     => $reorderPoint,
                'price'             => $price,
                'sold_30d'          => (int) $sold30d,
                'sold_7d'           => $sold7d,
                'daily_rotation'    => round($dailyRot, 2),
                'priority'          => $priority,
                'suggested_qty'     => (int) $suggestedQty,
                'days_of_stock'     => $daysOfStock,
                'projected_stockout'=> $projectedStockout,
                'suppliers'         => $suppliers,
            ];
        }, $rows);

        $healthScore = max(0, min(100,
            100 - ($criticalCount * 25 + $highCount * 10 + $mediumCount * 3)
        ));

        return response()->json([
            'data' => $data,
            'summary' => [
                'critical_count'      => $criticalCount,
                'high_count'          => $highCount,
                'medium_count'        => $mediumCount,
                'low_count'           => $lowCount,
                'total_restock_value' => round($totalRestockValue, 2),
                'health_score'        => $healthScore,
            ],
        ]);
    }
}
