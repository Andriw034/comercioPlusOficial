<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveMetricsController extends Controller
{
    public function snapshot(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $sid = (int) $store->id;

        // Single batch query — all metrics in one round-trip, zero N+1.
        $row = DB::selectOne("
            SELECT
                /* ── Ventas hoy ─────────────────────────────────────────── */
                COALESCE((
                    SELECT SUM(total) FROM orders
                    WHERE store_id = :s1
                      AND DATE(created_at) = CURDATE()
                      AND status NOT IN ('cancelled','payment_failed')
                ), 0) AS s_today,

                COALESCE((
                    SELECT COUNT(*) FROM orders
                    WHERE store_id = :s2
                      AND DATE(created_at) = CURDATE()
                      AND status NOT IN ('cancelled','payment_failed')
                ), 0) AS c_today,

                /* ── Ventas ayer (para % vs ayer) ────────────────────────── */
                COALESCE((
                    SELECT SUM(total) FROM orders
                    WHERE store_id = :s3
                      AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY
                      AND status NOT IN ('cancelled','payment_failed')
                ), 0) AS s_yesterday,

                /* ── Ventas esta semana ───────────────────────────────────── */
                COALESCE((
                    SELECT SUM(total) FROM orders
                    WHERE store_id = :s4
                      AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)
                      AND status NOT IN ('cancelled','payment_failed')
                ), 0) AS s_week,

                COALESCE((
                    SELECT COUNT(*) FROM orders
                    WHERE store_id = :s5
                      AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)
                      AND status NOT IN ('cancelled','payment_failed')
                ), 0) AS c_week,

                /* ── Pedidos activos (pending + processing) ──────────────── */
                (
                    SELECT COUNT(*) FROM orders
                    WHERE store_id = :s6
                      AND status IN ('pending','processing')
                ) AS active_orders,

                /* ── Stock bajo punto de reorden ─────────────────────────── */
                (
                    SELECT COUNT(*) FROM products
                    WHERE store_id = :s7
                      AND stock <= COALESCE(reorder_point, 5)
                ) AS low_stock,

                /* ── Top producto hoy (nombre ||| unidades) ──────────────── */
                (
                    SELECT CONCAT(p.name, '|||', SUM(op.quantity))
                    FROM order_products op
                    JOIN orders o  ON o.id  = op.order_id
                    JOIN products p ON p.id = op.product_id
                    WHERE o.store_id = :s8
                      AND DATE(o.created_at) = CURDATE()
                      AND o.status NOT IN ('cancelled','payment_failed')
                    GROUP BY op.product_id, p.name
                    ORDER BY SUM(op.quantity) DESC
                    LIMIT 1
                ) AS top_product_raw,

                /* ── Clientes nuevos hoy ─────────────────────────────────── */
                COALESCE((
                    SELECT COUNT(*) FROM customers
                    WHERE store_id = :s9
                      AND DATE(created_at) = CURDATE()
                ), 0) AS new_customers,

                /* ── Último pedido (id ||| total ||| status ||| min ago) ─── */
                (
                    SELECT CONCAT(
                        o.id, '|||',
                        COALESCE(o.total, 0), '|||',
                        o.status, '|||',
                        TIMESTAMPDIFF(MINUTE, o.created_at, NOW())
                    )
                    FROM orders o
                    WHERE o.store_id = :s10
                    ORDER BY o.created_at DESC
                    LIMIT 1
                ) AS last_order_raw
        ", [
            's1'  => $sid, 's2'  => $sid,
            's3'  => $sid, 's4'  => $sid,
            's5'  => $sid, 's6'  => $sid,
            's7'  => $sid, 's8'  => $sid,
            's9'  => $sid, 's10' => $sid,
        ]);

        // ── Parse top_product_raw ─────────────────────────────────────────
        $topProduct = null;
        if ($row->top_product_raw !== null) {
            $parts = explode('|||', (string) $row->top_product_raw, 2);
            if (count($parts) === 2) {
                $topProduct = [
                    'name'  => $parts[0],
                    'units' => (int) $parts[1],
                ];
            }
        }

        // ── Parse last_order_raw ──────────────────────────────────────────
        $lastOrder = null;
        if ($row->last_order_raw !== null) {
            $parts = explode('|||', (string) $row->last_order_raw, 4);
            if (count($parts) === 4) {
                $lastOrder = [
                    'id'          => (int)    $parts[0],
                    'total'       => (float)  $parts[1],
                    'status'      => (string) $parts[2],
                    'minutes_ago' => (int)    $parts[3],
                ];
            }
        }

        // ── % vs ayer ────────────────────────────────────────────────────
        $sToday     = (float) $row->s_today;
        $sYesterday = (float) $row->s_yesterday;
        $vsYesterdayPct = $sYesterday > 0
            ? round((($sToday - $sYesterday) / $sYesterday) * 100, 1)
            : ($sToday > 0 ? 100.0 : 0.0);

        return response()->json([
            'sales_today' => [
                'total'            => round($sToday, 2),
                'count'            => (int) $row->c_today,
                'vs_yesterday_pct' => $vsYesterdayPct,
            ],
            'sales_this_week' => [
                'total' => round((float) $row->s_week, 2),
                'count' => (int) $row->c_week,
            ],
            'active_orders'       => (int) $row->active_orders,
            'low_stock_count'     => (int) $row->low_stock,
            'top_product_today'   => $topProduct,
            'new_customers_today' => (int) $row->new_customers,
            'last_order'          => $lastOrder,
            'timestamp'           => Carbon::now()->toISOString(),
        ]);
    }
}
