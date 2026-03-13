<?php

namespace App\Services;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Validation\ValidationException;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(
            (string) config('services.mercadopago.access_token', '')
        );

        MercadoPagoConfig::setRuntimeEnviroment(
            config('services.mercadopago.sandbox')
                ? MercadoPagoConfig::LOCAL
                : MercadoPagoConfig::SERVER
        );
    }

    public function createPreference(array $order): array
    {
        $client = new PreferenceClient();

        $items = array_map(fn ($item) => [
            'id'         => (string) ($item['productId'] ?? uniqid()),
            'title'      => (string) ($item['name'] ?? 'Producto'),
            'quantity'   => (int) ($item['quantity'] ?? 0),
            'unit_price' => round((float) ($item['price'] ?? 0), 2),
            'currency_id'=> 'COP',
        ], $order['items'] ?? []);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'No hay items validos para crear la preferencia de pago.',
            ]);
        }

        foreach ($items as $item) {
            if ($item['quantity'] <= 0 || $item['unit_price'] <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Todos los productos deben tener cantidad y precio mayor a 0 para pagar con MercadoPago.',
                ]);
            }
        }

        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $isLocalhost = str_contains($frontendUrl, 'localhost') || str_contains($frontendUrl, '127.0.0.1');

        $appUrl      = rtrim((string) config('app.url'), '/');
        $isLocalApp  = str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1');

        $payload = [
            'items'              => $items,
            'payer'              => [
                'name'  => (string) ($order['customer']['name']  ?? ''),
                'email' => (string) ($order['customer']['email'] ?? ''),
                'phone' => ['number' => (string) ($order['customer']['phone'] ?? '')],
            ],
            'back_urls'          => [
                'success' => $frontendUrl . '/checkout/result?status=approved',
                'failure' => $frontendUrl . '/checkout/result?status=rejected',
                'pending' => $frontendUrl . '/checkout/result?status=pending',
            ],
            'external_reference'  => (string) $order['reference'],
            'statement_descriptor'=> 'ComercioPlus',
        ];

        // MP rechaza localhost para auto_return y notification_url
        if (! $isLocalhost) {
            $payload['auto_return'] = 'approved';
        }
        if (! $isLocalApp) {
            $payload['notification_url'] = $appUrl . '/api/payments/webhook';
        }

        $preference = $client->create($payload);

        $sandbox   = (bool) config('services.mercadopago.sandbox');
        $initPoint = $sandbox
            ? ($preference->sandbox_init_point ?? $preference->init_point)
            : $preference->init_point;

        return [
            'preference_id' => $preference->id,
            'init_point'    => $initPoint,
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $client  = new PaymentClient();
        $payment = $client->get((int) $paymentId);

        return [
            'id'                 => $payment->id,
            'status'             => $payment->status,
            'external_reference' => $payment->external_reference,
            'amount'             => $payment->transaction_amount,
        ];
    }
}
