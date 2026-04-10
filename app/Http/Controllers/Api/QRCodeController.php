<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    /**
     * Generar QR code para visualizar (SVG).
     */
    public function generate(Store $store)
    {
        $url = $store->full_url;

        $qr = (string) QrCode::format('svg')
                    ->size(500)
                    ->errorCorrection('H')
                    ->margin(2)
                    ->generate($url);

        return response($qr, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Descargar QR code (SVG).
     */
    public function download(Store $store)
    {
        $url = $store->full_url;

        $qr = (string) QrCode::format('svg')
                    ->size(1000)
                    ->errorCorrection('H')
                    ->margin(2)
                    ->generate($url);

        $filename = 'qr-' . ($store->subdomain ?? $store->slug ?? $store->id) . '-' . date('Y-m-d') . '.svg';

        return response($qr, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
