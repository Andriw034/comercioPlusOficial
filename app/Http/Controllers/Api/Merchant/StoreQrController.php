<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StoreQrController extends Controller
{
    public function show(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $url = $store->getStoreUrl();

        $svg = (string) QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        return response()->json([
            'success' => true,
            'data' => [
                'store_name' => $store->name,
                'store_url' => $url,
                'subdomain' => $store->subdomain,
                'qr_svg' => $svg,
            ],
        ]);
    }

    public function download(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una tienda registrada.',
            ], 404);
        }

        $url = $store->getStoreUrl();

        $svg = (string) QrCode::format('svg')
            ->size(600)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-' . ($store->slug ?? $store->id) . '.svg"',
        ]);
    }

    public function preview(Request $request, int $storeId)
    {
        $store = \App\Models\Store::findOrFail($storeId);

        $url = $store->getStoreUrl();

        $svg = (string) QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        return response()->json([
            'success' => true,
            'data' => [
                'store_name' => $store->name,
                'store_url' => $url,
                'qr_svg' => $svg,
            ],
        ]);
    }
}
