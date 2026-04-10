<?php

namespace App\Services\ElectronicInvoicing;

use App\Models\ElectronicDocument;

class CufeCalculator
{
    /**
     * Calculate CUFE (Código Único de Facturación Electrónica) using SHA-384.
     *
     * Follows DIAN Resolution algorithm:
     * NumFac + FecFac + HoraFac + ValFac + CodImp1 + ValImp1 + CodImp2 + ValImp2
     * + CodImp3 + ValImp3 + ValTot + NitOFE + NumAdq + ClTec + TipoAmbiente
     */
    public function calculate(ElectronicDocument $document): string
    {
        $data = $this->buildCufeString($document);
        return hash('sha384', $data);
    }

    /**
     * Calculate CUDE for credit/debit notes (same algorithm, different key).
     */
    public function calculateCude(ElectronicDocument $document): string
    {
        $data = $this->buildCudeString($document);
        return hash('sha384', $data);
    }

    /**
     * Get the raw pre-hash CUFE string (useful for debugging/testing).
     */
    public function getRawString(ElectronicDocument $document): string
    {
        return $this->buildCufeString($document);
    }

    private function buildCufeString(ElectronicDocument $document): string
    {
        $issueDate = $document->created_at->format('Y-m-d');
        $issueTime = $document->created_at->format('H:i:s-05:00');
        $technicalKey = config('invoicing.technical_key', '');

        $ivaTax = $this->getTaxAmount($document, 'iva');
        $incTax = $this->getTaxAmount($document, 'inc');
        $icaTax = $this->getTaxAmount($document, 'ica');

        return implode('', [
            $document->prefix . $document->number,          // NumFac
            $issueDate,                                     // FecFac
            $issueTime,                                     // HoraFac
            $this->fmt($document->subtotal),                // ValFac (base imponible)
            '01',                                           // CodImp1 (IVA)
            $this->fmt($ivaTax),                            // ValImp1
            '04',                                           // CodImp2 (INC)
            $this->fmt($incTax),                            // ValImp2
            '03',                                           // CodImp3 (ICA)
            $this->fmt($icaTax),                            // ValImp3
            $this->fmt($document->total),                   // ValTot
            $document->issuer_nit ?? '',                    // NitOFE
            $document->customer_identification ?? '',       // NumAdq
            $technicalKey,                                  // ClTec
            $this->environmentId(),                         // TipoAmbiente
        ]);
    }

    private function buildCudeString(ElectronicDocument $document): string
    {
        $issueDate = $document->created_at->format('Y-m-d');
        $issueTime = $document->created_at->format('H:i:s-05:00');
        $pin = config('invoicing.technical_key', '');

        $ivaTax = $this->getTaxAmount($document, 'iva');
        $incTax = $this->getTaxAmount($document, 'inc');
        $icaTax = $this->getTaxAmount($document, 'ica');

        return implode('', [
            $document->prefix . $document->number,
            $issueDate,
            $issueTime,
            $this->fmt($document->subtotal),
            '01',
            $this->fmt($ivaTax),
            '04',
            $this->fmt($incTax),
            '03',
            $this->fmt($icaTax),
            $this->fmt($document->total),
            $document->issuer_nit ?? '',
            $document->customer_identification ?? '',
            $pin,                                           // PIN en lugar de ClTec
            $this->environmentId(),
        ]);
    }

    private function getTaxAmount(ElectronicDocument $document, string $type): float
    {
        $document->loadMissing('taxes');

        return (float) $document->taxes
            ->where('tax_type', $type)
            ->sum('tax_amount');
    }

    private function fmt($value): string
    {
        return sprintf('%.2f', (float) $value);
    }

    private function environmentId(): string
    {
        return config('invoicing.environment') === 'production' ? '1' : '2';
    }
}
