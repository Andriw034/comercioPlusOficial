<?php

namespace App\Services;

use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentItem;
use App\Models\ElectronicDocumentLog;
use App\Models\ElectronicDocumentTax;
use App\Models\Order;
use App\Models\Store;
use App\Services\ElectronicInvoicing\CufeCalculator;
use App\Services\ElectronicInvoicing\MatiasApiClient;
use App\Services\ElectronicInvoicing\XmlGenerator;
use Illuminate\Support\Facades\DB;

class ElectronicInvoicingService
{
    public function __construct(
        protected XmlGenerator $xmlGenerator,
        protected CufeCalculator $cufeCalculator,
        protected MatiasApiClient $matiasClient,
    ) {}

    /**
     * Generate XML, calculate CUFE, send to DIAN via Matias API.
     *
     * @return array{success: bool, track_id: ?string, cufe: ?string, message: string}
     */
    public function sendToDian(ElectronicDocument $document, ?int $userId = null): array
    {
        if (!$document->isDraft() && $document->dian_status !== ElectronicDocument::STATUS_PENDING) {
            throw new \InvalidArgumentException(
                'Solo documentos en estado draft o pending pueden ser enviados a la DIAN.'
            );
        }

        $document->loadMissing(['items', 'taxes']);

        // 1. Calculate CUFE / CUDE
        $isCreditOrDebit = in_array($document->document_type, [
            ElectronicDocument::TYPE_CREDIT_NOTE,
            ElectronicDocument::TYPE_DEBIT_NOTE,
        ]);
        $cufe = $isCreditOrDebit
            ? $this->cufeCalculator->calculateCude($document)
            : $this->cufeCalculator->calculate($document);

        $document->update([
            'cufe' => $cufe,
        ]);

        // 2. Generate UBL 2.1 XML
        $xml = $this->xmlGenerator->generate($document);
        $document->update(['xml_content' => $xml]);

        // 3. Send to Matias API
        $result = match ($document->document_type) {
            ElectronicDocument::TYPE_CREDIT_NOTE => $this->matiasClient->sendCreditNote($xml),
            ElectronicDocument::TYPE_DEBIT_NOTE  => $this->matiasClient->sendDebitNote($xml),
            default                              => $this->matiasClient->sendInvoice($xml),
        };

        if ($result['success']) {
            $document->update([
                'dian_status'   => ElectronicDocument::STATUS_PENDING,
                'dian_track_id' => $result['track_id'] ?? null,
            ]);

            $this->log(
                $document, 'sent_to_dian',
                ElectronicDocument::STATUS_DRAFT, ElectronicDocument::STATUS_PENDING,
                'Documento enviado a la DIAN correctamente.',
                $userId,
                ['cufe' => $cufe, 'track_id' => $result['track_id'], 'message' => $result['message']],
            );
        } else {
            $this->log(
                $document, 'send_failed',
                $document->dian_status, $document->dian_status,
                'Error al enviar a la DIAN: ' . ($result['message'] ?? 'Error desconocido'),
                $userId,
                ['error' => $result['message']],
            );
        }

        return [
            'success'  => $result['success'],
            'track_id' => $result['track_id'] ?? null,
            'cufe'     => $cufe,
            'message'  => $result['message'] ?? '',
        ];
    }

    /**
     * Check the current DIAN status via Matias API and update locally.
     */
    public function checkDianStatus(ElectronicDocument $document, ?int $userId = null): array
    {
        if (!$document->dian_track_id) {
            return ['success' => false, 'message' => 'Documento sin track_id de DIAN.'];
        }

        $result = $this->matiasClient->getDocumentStatus($document->dian_track_id);

        if (!$result['success']) {
            return $result;
        }

        $dianStatus = match ($result['status'] ?? null) {
            'approved', 'valid'    => ElectronicDocument::STATUS_APPROVED,
            'rejected', 'invalid'  => ElectronicDocument::STATUS_REJECTED,
            default                => null,
        };

        if ($dianStatus && $dianStatus !== $document->dian_status) {
            $oldStatus = $document->dian_status;

            $updateData = [
                'dian_status'           => $dianStatus,
                'dian_response_message' => $result['message'] ?? null,
            ];
            if ($dianStatus === ElectronicDocument::STATUS_APPROVED) {
                $updateData['dian_approved_at'] = now();
            }

            $document->update($updateData);

            // Download signed XML if approved
            if ($dianStatus === ElectronicDocument::STATUS_APPROVED) {
                $signedXml = $this->matiasClient->downloadSignedXml($document->dian_track_id);
                if ($signedXml) {
                    $document->update(['xml_signed' => $signedXml]);
                }
            }

            $action = $dianStatus === ElectronicDocument::STATUS_APPROVED ? 'approved_by_dian' : 'rejected_by_dian';
            $this->log($document, $action, $oldStatus, $dianStatus, $result['message'] ?? null, $userId, $result['raw_response'] ?? null);
        }

        return $result;
    }

    /**
     * Crear factura electrónica en estado draft.
     */
    public function createInvoice(Store $store, array $data, ?int $userId = null): ElectronicDocument
    {
        return DB::transaction(function () use ($store, $data, $userId) {
            $number = $this->nextNumber($store, ElectronicDocument::TYPE_INVOICE);

            $doc = ElectronicDocument::create([
                'store_id'                     => $store->id,
                'order_id'                     => $data['order_id'] ?? null,
                'document_type'                => ElectronicDocument::TYPE_INVOICE,
                'prefix'                       => config('invoicing.prefixes.invoice'),
                'number'                       => $number,
                'dian_status'                  => ElectronicDocument::STATUS_DRAFT,
                'issuer_nit'                   => $data['issuer_nit'],
                'issuer_name'                  => $data['issuer_name'] ?? $store->name,
                'issuer_email'                 => $data['issuer_email'] ?? null,
                'issuer_phone'                 => $data['issuer_phone'] ?? null,
                'issuer_address'               => $data['issuer_address'] ?? null,
                'issuer_city'                  => $data['issuer_city'] ?? null,
                'issuer_department'            => $data['issuer_department'] ?? null,
                'customer_identification_type' => $data['customer_identification_type'],
                'customer_identification'      => $data['customer_identification'],
                'customer_name'                => $data['customer_name'],
                'customer_email'               => $data['customer_email'] ?? null,
                'customer_phone'               => $data['customer_phone'] ?? null,
                'customer_address'             => $data['customer_address'] ?? null,
                'customer_city'                => $data['customer_city'] ?? null,
                'customer_department'           => $data['customer_department'] ?? null,
                'payment_method'               => $data['payment_method'] ?? 'contado',
                'payment_means'                => $data['payment_means'] ?? 'efectivo',
                'payment_due_date'             => $data['payment_due_date'] ?? null,
                'currency'                     => config('invoicing.defaults.currency'),
                'notes'                        => $data['notes'] ?? null,
                'metadata'                     => $data['metadata'] ?? null,
            ]);

            // Crear items y calcular totales
            $this->createItems($doc, $data['items']);
            $this->consolidateTaxes($doc);
            $this->recalculateTotals($doc);

            $this->log($doc, 'created', null, ElectronicDocument::STATUS_DRAFT, 'Factura creada en borrador.', $userId);

            return $doc->fresh(['items', 'taxes']);
        });
    }

    /**
     * Crear factura desde una orden existente.
     */
    public function createFromOrder(Store $store, Order $order, array $issuerData, array $customerData, ?int $userId = null): ElectronicDocument
    {
        $items = $order->orderProducts->map(fn ($op) => [
            'product_id'  => $op->product_id,
            'description' => $op->product?->name ?? $op->product_name ?? 'Producto',
            'code'        => $op->product?->sku,
            'quantity'    => $op->quantity,
            'unit_price'  => $op->price,
            'discount'    => 0,
            'tax_type'    => 'IVA',
            'tax_rate'    => config('invoicing.defaults.tax_rate'),
        ])->toArray();

        return $this->createInvoice($store, array_merge($issuerData, $customerData, [
            'order_id' => $order->id,
            'items'    => $items,
        ]), $userId);
    }

    /**
     * Crear nota crédito referenciando una factura aprobada.
     */
    public function createCreditNote(ElectronicDocument $invoice, string $reason, array $items = [], ?int $userId = null): ElectronicDocument
    {
        if (!$invoice->isApproved()) {
            throw new \InvalidArgumentException('Solo se pueden crear notas crédito de facturas aprobadas.');
        }

        return DB::transaction(function () use ($invoice, $reason, $items, $userId) {
            $number = $this->nextNumber($invoice->store, ElectronicDocument::TYPE_CREDIT_NOTE);

            // Si no se pasan items, usar todos los de la factura original
            if (empty($items)) {
                $items = $invoice->items->map(fn ($item) => [
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'code'        => $item->code,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount,
                    'tax_type'    => $item->tax_type,
                    'tax_rate'    => $item->tax_rate,
                ])->toArray();
            }

            $doc = ElectronicDocument::create([
                'store_id'                     => $invoice->store_id,
                'order_id'                     => $invoice->order_id,
                'document_type'                => ElectronicDocument::TYPE_CREDIT_NOTE,
                'prefix'                       => config('invoicing.prefixes.credit_note'),
                'number'                       => $number,
                'dian_status'                  => ElectronicDocument::STATUS_DRAFT,
                'reference_document_id'        => $invoice->id,
                'issuer_nit'                   => $invoice->issuer_nit,
                'issuer_name'                  => $invoice->issuer_name,
                'issuer_email'                 => $invoice->issuer_email,
                'issuer_phone'                 => $invoice->issuer_phone,
                'issuer_address'               => $invoice->issuer_address,
                'issuer_city'                  => $invoice->issuer_city,
                'issuer_department'            => $invoice->issuer_department,
                'customer_identification_type' => $invoice->customer_identification_type,
                'customer_identification'      => $invoice->customer_identification,
                'customer_name'                => $invoice->customer_name,
                'customer_email'               => $invoice->customer_email,
                'customer_phone'               => $invoice->customer_phone,
                'customer_address'             => $invoice->customer_address,
                'customer_city'                => $invoice->customer_city,
                'customer_department'           => $invoice->customer_department,
                'payment_method'               => $invoice->payment_method,
                'payment_means'                => $invoice->payment_means,
                'currency'                     => $invoice->currency,
                'notes'                        => $reason,
            ]);

            $this->createItems($doc, $items);
            $this->consolidateTaxes($doc);
            $this->recalculateTotals($doc);

            $this->log($doc, 'created', null, ElectronicDocument::STATUS_DRAFT,
                "Nota crédito creada. Referencia: {$invoice->full_number}. Razón: {$reason}", $userId);

            return $doc->fresh(['items', 'taxes']);
        });
    }

    /**
     * Actualizar datos editables de un documento en draft/pending.
     */
    public function update(ElectronicDocument $doc, array $data, ?int $userId = null): ElectronicDocument
    {
        if (!$doc->canBeEdited()) {
            throw new \InvalidArgumentException('Este documento no puede ser editado en su estado actual.');
        }

        return DB::transaction(function () use ($doc, $data, $userId) {
            $editableFields = [
                'customer_identification_type', 'customer_identification', 'customer_name',
                'customer_email', 'customer_phone', 'customer_address', 'customer_city',
                'customer_department', 'payment_method', 'payment_means', 'payment_due_date', 'notes',
            ];

            $doc->update(array_intersect_key($data, array_flip($editableFields)));

            // Si se envían items, reconstruir
            if (isset($data['items'])) {
                $doc->items()->delete();
                $doc->taxes()->delete();
                $this->createItems($doc, $data['items']);
                $this->consolidateTaxes($doc);
                $this->recalculateTotals($doc);
            }

            $this->log($doc, 'updated', $doc->dian_status, $doc->dian_status, 'Documento actualizado.', $userId);

            return $doc->fresh(['items', 'taxes']);
        });
    }

    /**
     * Cambiar estado del documento (transiciones válidas).
     */
    public function changeStatus(ElectronicDocument $doc, string $newStatus, ?string $message = null, ?array $payload = null, ?int $userId = null): ElectronicDocument
    {
        $validTransitions = [
            ElectronicDocument::STATUS_DRAFT    => [ElectronicDocument::STATUS_PENDING],
            ElectronicDocument::STATUS_PENDING   => [ElectronicDocument::STATUS_APPROVED, ElectronicDocument::STATUS_REJECTED],
            ElectronicDocument::STATUS_APPROVED  => [ElectronicDocument::STATUS_CANCELLED],
            ElectronicDocument::STATUS_REJECTED  => [ElectronicDocument::STATUS_DRAFT],
        ];

        $allowed = $validTransitions[$doc->dian_status] ?? [];
        if (!in_array($newStatus, $allowed)) {
            throw new \InvalidArgumentException(
                "Transición no válida: {$doc->dian_status} → {$newStatus}"
            );
        }

        $oldStatus = $doc->dian_status;

        $updateData = ['dian_status' => $newStatus];
        if ($newStatus === ElectronicDocument::STATUS_APPROVED) {
            $updateData['dian_approved_at'] = now();
        }
        if ($message) {
            $updateData['dian_response_message'] = $message;
        }

        $doc->update($updateData);

        $action = match ($newStatus) {
            ElectronicDocument::STATUS_PENDING   => 'sent_to_dian',
            ElectronicDocument::STATUS_APPROVED  => 'approved',
            ElectronicDocument::STATUS_REJECTED  => 'rejected',
            ElectronicDocument::STATUS_CANCELLED => 'cancelled',
            ElectronicDocument::STATUS_DRAFT     => 'returned_to_draft',
            default => 'status_changed',
        };

        $this->log($doc, $action, $oldStatus, $newStatus, $message, $userId, $payload);

        return $doc->fresh();
    }

    // ─── Helpers privados ───

    private function nextNumber(Store $store, string $documentType): int
    {
        $last = ElectronicDocument::where('store_id', $store->id)
            ->where('document_type', $documentType)
            ->max('number');

        return ($last ?? 0) + 1;
    }

    private function createItems(ElectronicDocument $doc, array $items): void
    {
        foreach ($items as $i => $item) {
            $qty = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $discount = (float) ($item['discount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? config('invoicing.defaults.tax_rate'));

            $lineSubtotal = round($qty * $unitPrice - $discount, 2);
            $taxAmount = round($lineSubtotal * $taxRate / 100, 2);
            $lineTotal = round($lineSubtotal + $taxAmount, 2);

            ElectronicDocumentItem::create([
                'electronic_document_id' => $doc->id,
                'product_id'             => $item['product_id'] ?? null,
                'line_number'            => $i + 1,
                'code'                   => $item['code'] ?? null,
                'description'            => $item['description'],
                'unit_measure'           => $item['unit_measure'] ?? 'EA',
                'quantity'               => $qty,
                'unit_price'             => $unitPrice,
                'discount'               => $discount,
                'tax_amount'             => $taxAmount,
                'line_total'             => $lineTotal,
                'tax_type'               => $item['tax_type'] ?? 'IVA',
                'tax_rate'               => $taxRate,
            ]);
        }
    }

    private function consolidateTaxes(ElectronicDocument $doc): void
    {
        $doc->taxes()->delete();

        $groups = ElectronicDocumentItem::where('electronic_document_id', $doc->id)
            ->selectRaw('tax_type, tax_rate, SUM(line_total - tax_amount) as taxable, SUM(tax_amount) as tax_sum')
            ->groupBy('tax_type', 'tax_rate')
            ->get();

        foreach ($groups as $group) {
            ElectronicDocumentTax::create([
                'electronic_document_id' => $doc->id,
                'tax_type'               => $group->tax_type,
                'tax_rate'               => $group->tax_rate,
                'taxable_amount'         => round($group->taxable, 2),
                'tax_amount'             => round($group->tax_sum, 2),
            ]);
        }
    }

    private function recalculateTotals(ElectronicDocument $doc): void
    {
        $items = ElectronicDocumentItem::where('electronic_document_id', $doc->id)->get();

        $subtotal = $items->sum(fn ($i) => round($i->line_total - $i->tax_amount, 2));
        $taxTotal = $items->sum('tax_amount');
        $discountTotal = $items->sum('discount');

        $doc->update([
            'subtotal'       => round($subtotal, 2),
            'tax_total'      => round($taxTotal, 2),
            'discount_total' => round($discountTotal, 2),
            'total'          => round($subtotal + $taxTotal, 2),
        ]);
    }

    private function log(ElectronicDocument $doc, string $action, ?string $from, ?string $to, ?string $message = null, ?int $userId = null, ?array $payload = null): void
    {
        ElectronicDocumentLog::create([
            'electronic_document_id' => $doc->id,
            'user_id'                => $userId,
            'action'                 => $action,
            'status_from'            => $from,
            'status_to'              => $to,
            'message'                => $message,
            'payload'                => $payload,
            'ip_address'             => request()?->ip(),
        ]);
    }
}
