<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderBillingService
{
    private const PAID_STATUSES = ['paid', 'approved', 'completed'];

    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function createOrder(array $payload, int $userId): Order
    {
        return DB::transaction(function () use ($payload, $userId) {
            $storeId = (int) ($payload['store_id'] ?? 0);
            $store = Store::query()->with('taxSetting')->find($storeId);

            if (! $store) {
                throw ValidationException::withMessages([
                    'store_id' => 'La tienda indicada no existe.',
                ]);
            }

            $items = $this->normalizeItems($payload['items'] ?? []);
            if ($items === []) {
                throw ValidationException::withMessages([
                    'items' => 'Debes enviar al menos un item.',
                ]);
            }

            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = Product::query()
                ->where('store_id', $store->id)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw ValidationException::withMessages([
                    'items' => 'Uno o mas productos no existen o no pertenecen a la tienda.',
                ]);
            }

            $settings = $store->taxSetting;
            $taxEnabled = (bool) ($settings?->enable_tax ?? false) && (float) ($settings?->tax_rate ?? 0) > 0;
            $taxRate = $taxEnabled ? (float) $settings->tax_rate : 0.0;
            $roundingMode = (string) ($settings?->tax_rounding_mode ?? 'round');

            $lineRows = [];
            $subtotal = 0.0;
            $taxTotal = 0.0;
            $total = 0.0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = max(1, (int) $item['quantity']);

                if (! (bool) $product->allow_backorder && (int) $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$product->name}.",
                    ]);
                }

                // Regla de compatibilidad: product.price se trata como precio final.
                $unitPriceFinal = round((float) $product->price, 2);
                $lineTotal = round($unitPriceFinal * $quantity, 2);

                if ($taxEnabled && $taxRate > 0) {
                    $lineSubtotal = round($lineTotal / (1 + $taxRate), 2);
                    $lineTax = $this->applyRounding($lineTotal - $lineSubtotal, $roundingMode);
                    $lineTax = round($lineTax, 2);
                    $lineSubtotal = round($lineTotal - $lineTax, 2);
                } else {
                    $lineSubtotal = $lineTotal;
                    $lineTax = 0.0;
                }

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
                $total += $lineTotal;

                $lineRows[] = $this->filterOrderProductColumns([
                    'product_id' => (int) $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceFinal,
                    'base_price' => round($lineSubtotal, 2),
                    'tax_amount' => round($lineTax, 2),
                    'tax_rate_applied' => round($taxRate, 4),
                    'total_line' => round($lineTotal, 2),
                ]);
            }

            $subtotal = round($subtotal, 2);
            $taxTotal = round($taxTotal, 2);
            $total = round($total, 2);

            $requirePositiveTotal = (bool) ($payload['require_positive_total'] ?? false);
            if ($requirePositiveTotal && $total <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'El total de la orden debe ser mayor a 0 para procesar pagos en linea.',
                ]);
            }

            $status = $this->normalizeStatus((string) ($payload['status'] ?? 'pending'));
            $date = ! empty($payload['date']) ? $payload['date'] : now();

            $orderData = $this->filterOrderColumns([
                'user_id' => $userId,
                'store_id' => (int) $store->id,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'total_amount' => $total,
                'date' => $date,
                'payment_method' => (string) ($payload['payment_method'] ?? 'CARD'),
                'status' => $status,
                'channel' => (string) ($payload['channel'] ?? 'web'),
                'currency' => (string) ($payload['currency'] ?? 'COP'),
                'invoice_date' => now(),
            ]);

            $order = Order::query()->create($orderData);

            if (Schema::hasColumn('orders', 'invoice_number')) {
                $invoiceNumber = 'FAC-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
                $order->forceFill(['invoice_number' => $invoiceNumber])->save();
            }

            $order->ordenproducts()->createMany($lineRows);

            $order = $order->fresh(['user', 'store', 'ordenproducts.product']);

            if (in_array($status, self::PAID_STATUSES, true)) {
                $this->inventoryService->recordSale($order, $userId);
                $order = $order->fresh(['user', 'store', 'ordenproducts.product']);
            }

            if ((int) $userId !== (int) $store->user_id) {
                try {
                    $this->syncCustomerSnapshot((int) $store->id, (int) $userId, true);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo sincronizar customer snapshot al crear orden', [
                        'store_id' => (int) $store->id,
                        'user_id' => (int) $userId,
                        'order_id' => (int) $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $order;
        });
    }

    public function syncOrderCustomer(Order $order): void
    {
        $storeId = (int) ($order->store_id ?? 0);
        $userId = (int) ($order->user_id ?? 0);
        if ($storeId <= 0 || $userId <= 0) {
            return;
        }

        $storeOwnerId = (int) (Store::query()->whereKey($storeId)->value('user_id') ?? 0);
        if ($storeOwnerId > 0 && $storeOwnerId === $userId) {
            return;
        }

        $this->syncCustomerSnapshot($storeId, $userId, true);
    }

    public function syncStoreCustomersFromOrders(int $storeId): void
    {
        if ($storeId <= 0) {
            return;
        }

        $storeOwnerId = (int) (Store::query()->whereKey($storeId)->value('user_id') ?? 0);
        $userIds = Order::query()
            ->where('store_id', $storeId)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $rawUserId) {
            $userId = (int) $rawUserId;
            if ($userId <= 0 || ($storeOwnerId > 0 && $storeOwnerId === $userId)) {
                continue;
            }

            $this->syncCustomerSnapshot($storeId, $userId, false);
        }
    }

    public function syncCustomerSnapshot(int $storeId, int $userId, bool $reactivate = false): void
    {
        if ($storeId <= 0 || $userId <= 0) {
            return;
        }

        $hasIsActiveColumn = Schema::hasColumn('customers', 'is_active');

        $ordersQuery = Order::query()
            ->where('store_id', $storeId)
            ->where('user_id', $userId);

        $firstOrderAt = (clone $ordersQuery)->min('created_at');
        if (! $firstOrderAt) {
            return;
        }

        $lastOrderAt = (clone $ordersQuery)->max('created_at');
        $totalOrders = (int) (clone $ordersQuery)->count();
        $totalSpent = round((float) ((clone $ordersQuery)->whereIn('status', self::PAID_STATUSES)->sum('total') ?? 0), 2);

        $customer = Customer::query()->firstOrCreate(
            [
                'store_id' => $storeId,
                'user_id' => $userId,
            ],
            [
                'first_visited_at' => $firstOrderAt,
                'last_visited_at' => $lastOrderAt ?? $firstOrderAt,
            ]
        );

        $currentFirstVisitedAt = $customer->first_visited_at;
        if (! $currentFirstVisitedAt || strtotime((string) $firstOrderAt) < $currentFirstVisitedAt->getTimestamp()) {
            $customer->first_visited_at = $firstOrderAt;
        }

        $currentLastVisitedAt = $customer->last_visited_at;
        if (! $currentLastVisitedAt || ($lastOrderAt && strtotime((string) $lastOrderAt) > $currentLastVisitedAt->getTimestamp())) {
            $customer->last_visited_at = $lastOrderAt;
        }

        if ($hasIsActiveColumn && $reactivate && ! (bool) $customer->is_active) {
            $customer->is_active = true;
        }

        $customer->last_order_at = $lastOrderAt;
        $customer->total_orders = $totalOrders;
        $customer->total_spent = $totalSpent;
        $customer->save();
    }

    private function normalizeItems(array $rawItems): array
    {
        $items = [];

        foreach ($rawItems as $rawItem) {
            if (! is_array($rawItem)) {
                continue;
            }

            $productId = $rawItem['product_id'] ?? $rawItem['productId'] ?? null;
            $quantity = $rawItem['quantity'] ?? null;

            if (! is_numeric($productId) || ! is_numeric($quantity)) {
                continue;
            }

            $items[] = [
                'product_id' => (int) $productId,
                'quantity' => max(1, (int) $quantity),
            ];
        }

        return $items;
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $allowed = ['pending', 'processing', 'paid', 'approved', 'completed', 'cancelled'];

        return in_array($normalized, $allowed, true) ? $normalized : 'pending';
    }

    private function applyRounding(float $value, string $mode): float
    {
        $normalized = strtolower(trim($mode));

        return match ($normalized) {
            'ceil', 'up' => ceil($value * 100) / 100,
            'floor', 'down' => floor($value * 100) / 100,
            default => round($value, 2),
        };
    }

    private function filterOrderColumns(array $data): array
    {
        $columns = Schema::getColumnListing('orders');
        return array_intersect_key($data, array_flip($columns));
    }

    private function filterOrderProductColumns(array $data): array
    {
        $columns = Schema::getColumnListing('order_products');
        return array_intersect_key($data, array_flip($columns));
    }
}






