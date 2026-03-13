<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MercadoPagoService;
use App\Services\OrderBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoController extends Controller
{
    public function __construct(
        private readonly MercadoPagoService $mp,
        private readonly OrderBillingService $billing,
    ) {}

    /**
     * POST /api/payments/create-preference
     * Crea la orden en DB y devuelve la preference de MercadoPago.
     */
    public function createPreference(Request $request)
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user();
        if (! $user) {
            return response()->json(['error' => 'Debes iniciar sesion para continuar.'], 401);
        }

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.storeId' => 'required',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phone' => 'nullable|string',
            'customer.address' => 'nullable|string',
            'customer.city' => 'nullable|string',
            'customer.notes' => 'nullable|string',
            'paymentMethod' => 'nullable|string',
            'channel' => 'nullable|in:web,whatsapp,local',
        ]);

        $storeIds = collect($data['items'])
            ->map(fn (array $i) => (int) $i['storeId'])
            ->unique()
            ->values();

        if ($storeIds->count() !== 1) {
            return response()->json(['error' => 'La orden debe contener productos de una sola tienda.'], 422);
        }

        try {
            $items = collect($data['items'])->map(fn (array $i) => [
                'product_id' => (int) $i['productId'],
                'quantity' => (int) $i['quantity'],
            ])->values()->all();

            $order = $this->billing->createOrder([
                'store_id' => (int) $storeIds->first(),
                'items' => $items,
                'payment_method' => $data['paymentMethod'] ?? 'mercadopago',
                'status' => 'pending',
                'channel' => $data['channel'] ?? 'web',
                'currency' => 'COP',
                'require_positive_total' => true,
            ], (int) $user->id);

            $customer = $data['customer'];
            $order->update([
                'customer_email' => $customer['email'],
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'] ?? null,
                'customer_address' => $customer['address'] ?? null,
                'customer_city' => $customer['city'] ?? null,
            ]);

            $reference = 'MP-' . $order->id . '-' . time();

            $order->update([
                'payment_reference' => $reference,
                'payment_status' => 'pending',
            ]);

            $order->loadMissing('ordenproducts.product');

            $mpItems = $order->ordenproducts
                ->map(fn ($line) => [
                    'productId' => (int) $line->product_id,
                    'name' => (string) ($line->product?->name ?? ('Producto #' . $line->product_id)),
                    'quantity' => (int) $line->quantity,
                    'price' => round((float) ($line->unit_price ?? 0), 2),
                ])
                ->values()
                ->all();

            if ($mpItems === []) {
                throw ValidationException::withMessages([
                    'items' => 'No se pudieron generar items validos para el pago.',
                ]);
            }

            $preference = $this->mp->createPreference([
                'items' => $mpItems,
                'customer' => $customer,
                'reference' => $reference,
            ]);

            Log::info("MP preference created for order #{$order->id}, ref: {$reference}");

            return response()->json([
                'orderId' => $order->id,
                'reference' => $reference,
                'preference_id' => $preference['preference_id'],
                'init_point' => $preference['init_point'],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->flatten()->first()], 422);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse?->getStatusCode();
            $content = $apiResponse?->getContent();
            $mpMessage = $this->extractMpErrorMessage($content);

            Log::error('MP createPreference API error', [
                'message' => $e->getMessage(),
                'status' => $statusCode,
                'content' => $content,
            ]);

            return response()->json([
                'error' => $mpMessage
                    ? "MercadoPago rechazo la solicitud: {$mpMessage}"
                    : 'MercadoPago no pudo crear la preferencia. Verifica los datos de la orden.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('MP createPreference unexpected error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Error al procesar el pago. Intenta nuevamente.'], 500);
        }
    }

    private function extractMpErrorMessage(mixed $content): ?string
    {
        if (is_array($content)) {
            $candidate = data_get($content, 'message')
                ?? data_get($content, 'error')
                ?? data_get($content, 'cause.0.description')
                ?? data_get($content, 'cause.0.code');

            if (is_scalar($candidate)) {
                $text = trim((string) $candidate);
                return $text !== '' ? $text : null;
            }

            return null;
        }

        if (is_string($content)) {
            $text = trim($content);
            return $text !== '' ? $text : null;
        }

        return null;
    }

    /**
     * POST /api/payments/webhook
     * Recibe notificaciones IPN de MercadoPago.
     */
    public function webhook(Request $request)
    {
        $type = $request->query('type') ?? $request->input('type');
        $dataId = $request->query('data_id')
            ?? $request->input('data.id')
            ?? $request->input('data_id');

        Log::info('MP webhook received', ['type' => $type, 'data_id' => $dataId]);

        if ($type !== 'payment' || ! $dataId) {
            return response()->json(['ok' => true]);
        }

        try {
            $payment = $this->mp->getPayment((string) $dataId);

            $status = match ($payment['status']) {
                'approved' => 'paid',
                'rejected', 'cancelled' => 'cancelled',
                default => 'pending',
            };

            $order = Order::where('payment_reference', $payment['external_reference'])->first();

            if ($order) {
                if ($status === 'paid') {
                    $order->markAsPaid(['id' => $payment['id'], 'status' => $payment['status']]);
                } elseif ($status === 'cancelled') {
                    $order->markAsFailed($payment['status']);
                } else {
                    $order->update([
                        'payment_status' => $payment['status'],
                        'updated_at' => now(),
                    ]);
                }

                try {
                    $this->billing->syncOrderCustomer($order->fresh());
                } catch (\Throwable $e) {
                    Log::warning('MP webhook customer sync warning', [
                        'order_id' => (int) $order->id,
                        'status' => $status,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info("MP webhook: order #{$order->id} -> {$status}");
            }
        } catch (\Exception $e) {
            Log::error('MP webhook error: ' . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/payments/result?reference={MP-xxx-yyy}
     * Consultado por el frontend tras el redirect de MercadoPago.
     */
    public function result(Request $request)
    {
        $reference = $request->query('external_reference')
            ?? $request->query('reference');

        if (! $reference) {
            return response()->json(['error' => 'No reference'], 400);
        }

        $order = Order::where('payment_reference', $reference)->first();

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'status' => strtoupper($order->payment_status ?? $order->status ?? 'PENDING'),
            'reference' => $reference,
            'amount' => $order->total,
            'currency' => $order->currency ?? 'COP',
            'orderId' => $order->id,
            'customerName' => $order->customer_name,
        ]);
    }
}

