<?php

namespace App\Services\ElectronicInvoicing;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MatiasApiClient
{
    private function client(): PendingRequest
    {
        $client = Http::baseUrl(config('invoicing.matias_api.base_url'))
            ->withToken(config('invoicing.matias_api.api_key'))
            ->acceptJson()
            ->timeout(30);

        // TEMPORAL: Skip SSL verification in test environment (expired cert)
        if (config('invoicing.environment') === 'test') {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Send an invoice XML to DIAN via Matias API.
     *
     * @return array{success: bool, track_id: ?string, cufe: ?string, message: string, raw_response?: array}
     */
    public function sendInvoice(string $xmlContent): array
    {
        return $this->send('/api/ubl2.1/invoice', $xmlContent);
    }

    /**
     * Send a credit note XML to DIAN via Matias API.
     *
     * @return array{success: bool, track_id: ?string, cufe: ?string, message: string, raw_response?: array}
     */
    public function sendCreditNote(string $xmlContent): array
    {
        return $this->send('/api/ubl2.1/credit-note', $xmlContent);
    }

    /**
     * Send a debit note XML to DIAN via Matias API.
     *
     * @return array{success: bool, track_id: ?string, cufe: ?string, message: string, raw_response?: array}
     */
    public function sendDebitNote(string $xmlContent): array
    {
        return $this->send('/api/ubl2.1/debit-note', $xmlContent);
    }

    /**
     * Get the processing status of a document by its track ID.
     *
     * @return array{success: bool, status: ?string, message: string, is_valid: ?bool, raw_response?: array}
     */
    public function getDocumentStatus(string $trackId): array
    {
        try {
            $response = $this->client()->get("/api/ubl2.1/status/{$trackId}");

            if ($response->failed()) {
                Log::warning('MATIAS API: status check failed', [
                    'track_id' => $trackId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                return [
                    'success'  => false,
                    'status'   => null,
                    'message'  => "HTTP {$response->status()}: " . $response->body(),
                    'is_valid' => null,
                ];
            }

            $data = $response->json();

            return [
                'success'      => true,
                'status'       => $data['status'] ?? null,
                'message'      => $data['message'] ?? 'Consulta exitosa',
                'is_valid'     => $data['is_valid'] ?? null,
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('MATIAS API: status exception', [
                'track_id' => $trackId,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success'  => false,
                'status'   => null,
                'message'  => $e->getMessage(),
                'is_valid' => null,
            ];
        }
    }

    /**
     * Download the signed XML returned by DIAN.
     */
    public function downloadSignedXml(string $trackId): ?string
    {
        try {
            $response = $this->client()->get("/api/ubl2.1/status/xml/{$trackId}");

            if ($response->failed()) {
                Log::warning('MATIAS API: signed XML download failed', [
                    'track_id' => $trackId,
                    'status'   => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $xml  = $data['xml'] ?? null;

            return $xml ? base64_decode($xml) : null;
        } catch (\Throwable $e) {
            Log::error('MATIAS API: downloadSignedXml exception', [
                'track_id' => $trackId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send XML to a specific endpoint.
     *
     * @return array{success: bool, track_id: ?string, cufe: ?string, message: string, raw_response?: array}
     */
    private function send(string $endpoint, string $xmlContent): array
    {
        $isTest = config('invoicing.environment') === 'test';

        try {
            $response = $this->client()->post($endpoint, [
                'xml'       => base64_encode($xmlContent),
                'test_mode' => $isTest,
            ]);

            if ($response->failed()) {
                Log::warning('MATIAS API: send failed', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                return [
                    'success'  => false,
                    'track_id' => null,
                    'cufe'     => null,
                    'message'  => "HTTP {$response->status()}: " . $response->body(),
                ];
            }

            $data = $response->json();

            return [
                'success'      => true,
                'track_id'     => $data['track_id'] ?? null,
                'cufe'         => $data['cufe'] ?? null,
                'message'      => $data['message'] ?? 'Enviado correctamente',
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('MATIAS API: send exception', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success'  => false,
                'track_id' => null,
                'cufe'     => null,
                'message'  => $e->getMessage(),
            ];
        }
    }
}
