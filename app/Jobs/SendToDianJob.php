<?php

namespace App\Jobs;

use App\Models\ElectronicDocument;
use App\Services\ElectronicInvoicingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendToDianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var int[] Backoff in seconds: 1 min, 5 min, 10 min */
    public array $backoff = [60, 300, 600];

    public function __construct(
        public ElectronicDocument $document,
        public ?int $userId = null,
    ) {}

    public function handle(ElectronicInvoicingService $service): void
    {
        $result = $service->sendToDian($this->document, $this->userId);

        if (!$result['success']) {
            Log::warning('SendToDianJob: envío falló', [
                'document_id' => $this->document->id,
                'error'       => $result['message'],
                'attempt'     => $this->attempts(),
            ]);

            // Rethrow so queue retries
            throw new \RuntimeException('DIAN send failed: ' . $result['message']);
        }

        Log::info('SendToDianJob: enviado exitosamente', [
            'document_id' => $this->document->id,
            'track_id'    => $result['track_id'],
            'cufe'        => $result['cufe'],
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('SendToDianJob: falló después de todos los reintentos', [
            'document_id' => $this->document->id,
            'error'       => $exception?->getMessage(),
        ]);

        $this->document->logs()->create([
            'electronic_document_id' => $this->document->id,
            'user_id'                => $this->userId,
            'action'                 => 'send_failed_permanent',
            'status_from'            => $this->document->dian_status,
            'status_to'              => $this->document->dian_status,
            'message'                => 'Envío a DIAN falló después de ' . $this->tries . ' intentos: ' . $exception?->getMessage(),
        ]);
    }
}
