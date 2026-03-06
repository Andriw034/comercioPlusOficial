<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\AutoRestockSetting;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StockPrediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoRestockController extends Controller
{
    /**
     * GET /merchant/restock
     * Products with stock at or below their reorder_point, excluding dismissed ones.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store()->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $setting = AutoRestockSetting::query()
            ->where('store_id', (int) $store->id)
            ->first();

        $excluded = $setting?->excluded_product_ids ?? [];

        $products = Product::query()
            ->where('store_id', (int) $store->id)
            ->where('stock', '>=', 0)
            ->where(function ($q) use ($setting) {
                $threshold = (int) ($setting?->min_stock_threshold ?? 0);
                // Show products below their own reorder_point OR below the store threshold
                $q->whereNotNull('reorder_point')
                    ->whereColumn('stock', '<=', 'reorder_point');
                if ($threshold > 0) {
                    $q->orWhere('stock', '<=', $threshold);
                }
            })
            ->when(count($excluded) > 0, fn ($q) => $q->whereNotIn('id', $excluded))
            ->orderBy('stock')
            ->get(['id', 'name', 'sku', 'stock', 'reorder_point', 'cost_price', 'image_url', 'image_path', 'image']);

        $predictionsByProductId = StockPrediction::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $data = $products->map(function (Product $product) use ($setting, $predictionsByProductId) {
            $prediction = $predictionsByProductId[(int) $product->id] ?? null;
            $threshold = (int) ($product->reorder_point ?? $setting?->min_stock_threshold ?? 5);

            return [
                'product_id' => (int) $product->id,
                'name' => (string) $product->name,
                'sku' => $product->sku,
                'stock' => (int) $product->stock,
                'reorder_point' => $threshold,
                'cost_price' => (float) ($product->cost_price ?? 0),
                'image_url' => $product->image_url ?? $product->image_path ?? $product->image,
                'setting' => [
                    'min_stock_threshold' => $threshold,
                    'days_of_stock_target' => (int) ($setting?->days_of_stock_target ?? 30),
                    'auto_approve' => (bool) ($setting?->auto_approve ?? false),
                    'supplier_email' => $setting?->supplier_email,
                    'supplier_whatsapp' => $setting?->supplier_whatsapp,
                ],
                'prediction' => $prediction ? [
                    'days_until_depletion' => (int) ($prediction->predicted_days_until_depletion ?? 0),
                    'avg_daily_sales' => round((float) ($prediction->avg_daily_sales ?? 0), 2),
                    'recommended_restock_qty' => (int) ($prediction->recommended_restock_quantity ?? 0),
                ] : null,
            ];
        });

        return response()->json([
            'message' => 'Productos con stock critico',
            'data' => $data->values(),
            'meta' => [
                'total' => $data->count(),
                'store_setting' => $setting ? [
                    'enabled' => (bool) $setting->enabled,
                    'min_stock_threshold' => (int) $setting->min_stock_threshold,
                    'days_of_stock_target' => (int) ($setting->days_of_stock_target ?? 30),
                    'auto_approve' => (bool) $setting->auto_approve,
                    'supplier_email' => $setting->supplier_email,
                    'supplier_whatsapp' => $setting->supplier_whatsapp,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /merchant/restock/{product}
     * Per-product restock configuration.
     */
    public function settings(Request $request, Product $product): JsonResponse
    {
        $authError = $this->authorizeProduct($request, $product);
        if ($authError !== null) {
            return $authError;
        }

        $store = $request->user()->store()->first();
        $setting = AutoRestockSetting::query()
            ->where('store_id', (int) $store->id)
            ->first();

        return response()->json([
            'message' => 'Configuracion de reabastecimiento',
            'data' => [
                'product_id' => (int) $product->id,
                'name' => (string) $product->name,
                'stock' => (int) $product->stock,
                'min_stock_threshold' => (int) ($product->reorder_point ?? $setting?->min_stock_threshold ?? 5),
                'cost_price' => (float) ($product->cost_price ?? 0),
                'setting' => $setting ? [
                    'days_of_stock_target' => (int) ($setting->days_of_stock_target ?? 30),
                    'auto_approve' => (bool) $setting->auto_approve,
                    'supplier_email' => $setting->supplier_email,
                    'supplier_whatsapp' => $setting->supplier_whatsapp,
                    'frequency' => $setting->frequency,
                ] : null,
            ],
        ]);
    }

    /**
     * PUT /merchant/restock/{product}
     * Save per-product threshold and store-level supplier settings.
     */
    public function saveSettings(Request $request, Product $product): JsonResponse
    {
        $authError = $this->authorizeProduct($request, $product);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'min_stock_threshold' => 'required|integer|min:0|max:99999',
            'days_of_stock_target' => 'nullable|integer|min:1|max:365',
            'auto_approve' => 'nullable|boolean',
            'supplier_email' => 'nullable|email|max:191',
            'supplier_whatsapp' => 'nullable|string|max:30',
        ]);

        $store = $request->user()->store()->first();

        DB::transaction(function () use ($payload, $product, $store) {
            $product->reorder_point = (int) $payload['min_stock_threshold'];
            $product->save();

            $settingData = array_filter([
                'enabled' => true,
                'min_stock_threshold' => (int) $payload['min_stock_threshold'],
                'days_of_stock_target' => isset($payload['days_of_stock_target']) ? (int) $payload['days_of_stock_target'] : null,
                'auto_approve' => isset($payload['auto_approve']) ? (bool) $payload['auto_approve'] : null,
                'supplier_email' => $payload['supplier_email'] ?? null,
                'supplier_whatsapp' => $payload['supplier_whatsapp'] ?? null,
            ], fn ($v) => $v !== null);

            AutoRestockSetting::query()->updateOrCreate(
                ['store_id' => (int) $store->id],
                $settingData,
            );
        });

        return response()->json([
            'message' => 'Configuracion guardada',
            'data' => [
                'product_id' => (int) $product->id,
                'min_stock_threshold' => (int) $payload['min_stock_threshold'],
            ],
        ]);
    }

    /**
     * POST /merchant/restock/{product}/request
     * Create a purchase request for this product.
     */
    public function request(Request $request, Product $product): JsonResponse
    {
        $authError = $this->authorizeProduct($request, $product);
        if ($authError !== null) {
            return $authError;
        }

        $payload = $request->validate([
            'ordered_qty' => 'nullable|integer|min:1|max:99999',
            'note' => 'nullable|string|max:500',
        ]);

        $store = $request->user()->store()->first();
        $setting = AutoRestockSetting::query()->where('store_id', (int) $store->id)->first();

        $prediction = StockPrediction::query()->where('product_id', (int) $product->id)->first();

        $daysTarget = (int) ($setting?->days_of_stock_target ?? 30);
        $avgDailySales = (float) ($prediction?->avg_daily_sales ?? 1);
        $suggestedQty = $prediction?->recommended_restock_quantity
            ?? max(1, (int) round($avgDailySales * $daysTarget));
        $orderedQty = isset($payload['ordered_qty']) ? (int) $payload['ordered_qty'] : $suggestedQty;

        $purchaseRequest = DB::transaction(function () use ($product, $store, $setting, $suggestedQty, $orderedQty, $payload) {
            $pr = PurchaseRequest::query()->create([
                'store_id' => (int) $store->id,
                'status' => $setting?->auto_approve
                    ? PurchaseRequest::STATUS_SENT
                    : PurchaseRequest::STATUS_DRAFT,
                'generation_type' => PurchaseRequest::GENERATION_AUTOMATIC,
                'notes' => $payload['note'] ?? null,
                'generated_at' => now(),
                'created_by' => (int) request()->user()->id,
            ]);

            PurchaseRequestItem::query()->create([
                'purchase_request_id' => (int) $pr->id,
                'product_id' => (int) $product->id,
                'current_stock' => (int) $product->stock,
                'suggested_qty' => (int) $suggestedQty,
                'ordered_qty' => (int) $orderedQty,
                'last_cost' => (float) ($product->cost_price ?? 0),
            ]);

            return $pr;
        });

        $waUrl = null;
        $whatsapp = trim((string) ($setting?->supplier_whatsapp ?? ''));
        if ($whatsapp !== '') {
            $phone = preg_replace('/[^0-9]/', '', $whatsapp);
            $message = rawurlencode(
                "Hola, necesito reabastecer el producto: *{$product->name}*.\n"
                . "Stock actual: {$product->stock} unidades.\n"
                . "Cantidad solicitada: {$orderedQty} unidades.\n"
                . "Solicitud #PR-{$purchaseRequest->id} — ComercioPlus"
            );
            $waUrl = "https://wa.me/{$phone}?text={$message}";
        }

        return response()->json([
            'message' => 'Solicitud de reabastecimiento creada',
            'data' => [
                'purchase_request_id' => (int) $purchaseRequest->id,
                'product_id' => (int) $product->id,
                'ordered_qty' => $orderedQty,
                'status' => (string) $purchaseRequest->status,
                'whatsapp_url' => $waUrl,
            ],
        ], 201);
    }

    /**
     * POST /merchant/restock/{product}/dismiss
     * Exclude the product from restock alerts.
     */
    public function dismiss(Request $request, Product $product): JsonResponse
    {
        $authError = $this->authorizeProduct($request, $product);
        if ($authError !== null) {
            return $authError;
        }

        $store = $request->user()->store()->first();

        $setting = AutoRestockSetting::query()->firstOrCreate(
            ['store_id' => (int) $store->id],
            [
                'enabled' => true,
                'min_stock_threshold' => 5,
                'excluded_product_ids' => [],
            ],
        );

        $setting->excludeProduct((int) $product->id);

        return response()->json([
            'message' => 'Producto excluido de alertas de reabastecimiento',
            'data' => ['product_id' => (int) $product->id],
        ]);
    }

    private function authorizeProduct(Request $request, Product $product): ?JsonResponse
    {
        $store = $request->user()->store()->first();
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $product->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }
}
