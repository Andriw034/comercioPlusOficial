<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WompiController extends Controller
{
    private $publicKey;
    private $privateKey;
    private $apiUrl;
    private $eventsSecret;

    public function __construct()
    {
        $this->publicKey = config('services.wompi.public_key');
        $this->privateKey = config('services.wompi.private_key');
        $this->apiUrl = config('services.wompi.api_url', 'https://production.wompi.co/v1');
        $this->eventsSecret = config('services.wompi.events_secret');
    }

    /**
     * 1. CREAR ORDEN
     * POST /api/orders/create
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.storeId' => 'required',
            'customer.email' => 'required|email',
            'customer.name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.address' => 'nullable|string',
            'customer.city' => 'nullable|string',
            'totalAmount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:PSE,NEQUI,BANCOLOMBIA,CARD',
        ]);

        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'items' => $validated['items'],
                'customer_email' => $validated['customer']['email'],
                'customer_name' => $validated['customer']['name'],
                'customer_phone' => $validated['customer']['phone'],
                'customer_address' => $validated['customer']['address'] ?? null,
                'customer_city' => $validated['customer']['city'] ?? null,
                'total_amount' => $validated['totalAmount'],
                'payment_method' => $validated['paymentMethod'],
                'status' => 'pending',
            ]);

            return response()->json([
                'orderId' => $order->id,
                'message' => 'Orden creada exitosamente',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al crear la orden',
            ], 500);
        }
    }

    /**
     * 2. CREAR TRANSACCIÓN EN WOMPI
     * POST /api/payments/wompi/create
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'orderId' => 'required|exists:orders,id',
            'amount' => 'required|integer', // En centavos
            'currency' => 'required|string',
            'paymentMethod' => 'required|string',
            'customer' => 'required|array',
            'redirectUrl' => 'required|url',
        ]);

        try {
            $order = Order::findOrFail($validated['orderId']);

            // Generar referencia única
            $reference = 'ORDER-' . $order->id . '-' . time();

            // Preparar datos de transacción
            $transactionData = [
                'public_key' => $this->publicKey,
                'currency' => $validated['currency'],
                'amount_in_cents' => $validated['amount'],
                'reference' => $reference,
                'redirect_url' => $validated['redirectUrl'],
                'customer_data' => [
                    'email' => $validated['customer']['email'],
                    'full_name' => $validated['customer']['fullName'],
                    'phone_number' => $validated['customer']['phoneNumber'],
                    'phone_number_prefix' => '+57',
                ],
            ];

            // Configuración específica por método de pago
            switch ($validated['paymentMethod']) {
                case 'PSE':
                    $transactionData['payment_method'] = [
                        'type' => 'PSE',
                        'user_type' => '0', // 0 = Persona, 1 = Empresa
                        'user_legal_id_type' => 'CC',
                        'user_legal_id' => $validated['customer']['document'] ?? '000000000',
                        'financial_institution_code' => '',
                        'payment_description' => "Orden #{$order->id}",
                    ];
                    break;

                case 'NEQUI':
                    $transactionData['payment_method'] = [
                        'type' => 'NEQUI',
                        'phone_number' => $validated['customer']['phoneNumber'],
                    ];
                    break;

                case 'BANCOLOMBIA':
                    $transactionData['payment_method'] = [
                        'type' => 'BANCOLOMBIA_TRANSFER',
                    ];
                    break;

                case 'CARD':
                    $transactionData['payment_method'] = [
                        'type' => 'CARD',
                    ];
                    break;
            }

            // Crear transacción en Wompi
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/transactions', $transactionData);

            if (!$response->successful()) {
                Log::error('Wompi API Error: ' . $response->body());
                return response()->json([
                    'error' => 'Error al procesar el pago',
                    'details' => $response->json('error.messages') ?? $response->body(),
                ], 500);
            }

            $wompiData = $response->json('data');

            // Actualizar orden con datos de Wompi
            $order->update([
                'payment_reference' => $reference,
                'wompi_transaction_id' => $wompiData['id'],
                'payment_status' => 'processing',
            ]);

            return response()->json([
                'checkoutUrl' => $wompiData['payment_link_url'] ?? $wompiData['redirect_url'],
                'transactionId' => $wompiData['id'],
                'reference' => $reference,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating Wompi payment: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al procesar el pago',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. WEBHOOK DE WOMPI
     * POST /api/payments/wompi/webhook
     */
    public function webhook(Request $request)
    {
        try {
            $event = $request->all();
            $signature = $request->header('X-Event-Signature');

            // Validar signature
            if (!$this->validateSignature($event, $signature)) {
                Log::error('Invalid Wompi signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            Log::info('Wompi webhook event: ' . $event['event']);

            // Procesar evento
            switch ($event['event']) {
                case 'transaction.updated':
                    $this->handleTransactionUpdate($event['data']['transaction']);
                    break;

                case 'transaction.created':
                    Log::info('Transaction created: ' . $event['data']['transaction']['id']);
                    break;

                default:
                    Log::info('Unknown event: ' . $event['event']);
            }

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Manejar actualización de transacción
     */
    private function handleTransactionUpdate($transaction)
    {
        $reference = $transaction['reference'];
        $status = $transaction['status'];
        $id = $transaction['id'];

        Log::info("Transaction {$id} updated to status: {$status}");

        // Encontrar orden por referencia
        $order = Order::where('payment_reference', $reference)->first();

        if (!$order) {
            Log::error('Order not found for reference: ' . $reference);
            return;
        }

        switch ($status) {
            case 'APPROVED':
                $order->markAsPaid($transaction);

                // Enviar email de confirmación
                $this->sendOrderConfirmationEmail($order);

                // Notificar a vendedores
                $this->notifySellerNewOrder($order);

                Log::info("Order {$order->id} marked as PAID");
                break;

            case 'DECLINED':
            case 'ERROR':
                $order->markAsFailed($transaction['status_message'] ?? null);
                Log::info("Order {$order->id} payment FAILED");
                break;

            case 'PENDING':
                $order->update(['payment_status' => 'pending']);
                break;
        }
    }

    /**
     * Validar signature del webhook
     */
    private function validateSignature($event, $signature)
    {
        if (!$signature || !$this->eventsSecret) {
            return false;
        }

        $payload = $event['data']['transaction']['id'] .
                   $event['data']['transaction']['status'] .
                   $event['timestamp'];

        $expectedSignature = hash_hmac('sha256', $payload, $this->eventsSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * 4. CONSULTAR ESTADO DE TRANSACCIÓN
     * GET /api/payments/wompi/status/{transactionId}
     */
    public function getTransactionStatus($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->publicKey,
            ])->get($this->apiUrl . '/transactions/' . $transactionId);

            if (!$response->successful()) {
                return response()->json(['error' => 'Error al consultar estado'], 500);
            }

            return response()->json($response->json('data'));
        } catch (\Exception $e) {
            Log::error('Error fetching transaction: ' . $e->getMessage());
            return response()->json(['error' => 'Error al consultar estado'], 500);
        }
    }

    /**
     * 5. OBTENER BANCOS PARA PSE
     * GET /api/payments/wompi/pse-banks
     */
    public function getPseBanks()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->publicKey,
            ])->get($this->apiUrl . '/pse/financial_institutions');

            if (!$response->successful()) {
                return response()->json(['error' => 'Error al obtener bancos'], 500);
            }

            return response()->json($response->json('data'));
        } catch (\Exception $e) {
            Log::error('Error fetching PSE banks: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener bancos'], 500);
        }
    }

    /**
     * Enviar email de confirmación
     */
    private function sendOrderConfirmationEmail($order)
    {
        // Implementar envío de email
        // Mail::to($order->customer_email)->send(new OrderConfirmed($order));
        Log::info("Email sent to {$order->customer_email}");
    }

    /**
     * Notificar a vendedores
     */
    private function notifySellerNewOrder($order)
    {
        // Implementar notificación a vendedores
        Log::info("Sellers notified for order {$order->id}");
    }
}
