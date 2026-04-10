<?php

namespace App\Services;

use App\Models\SimpleReceipt;
use Barryvdh\DomPDF\Facade\Pdf;

class SimpleReceiptPdfService
{
    public function generate(SimpleReceipt $receipt)
    {
        $receipt->loadMissing(['store', 'order']);

        $data = [
            'receipt'    => $receipt,
            'store'      => $receipt->store,
            'order'      => $receipt->order,
            'items'      => $receipt->order->items ?? [],
            'generated'  => now()->format('d/m/Y H:i'),
        ];

        return Pdf::loadView('pdf.simple-receipt', $data)
            ->setPaper('letter')
            ->setOption('defaultFont', 'sans-serif');
    }
}
