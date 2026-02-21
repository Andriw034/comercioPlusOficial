<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTaxSettingRequest;
use App\Models\Store;
use App\Models\StoreTaxSetting;
use Illuminate\Http\JsonResponse;

class TaxSettingController extends Controller
{
    private const UI_TO_DB_ROUNDING = [
        'HALF_UP' => 'round',
        'HALF_EVEN' => 'round',
        'DOWN' => 'floor',
        'UP' => 'ceil',
    ];

    private const DB_TO_UI_ROUNDING = [
        'round' => 'HALF_UP',
        'ceil' => 'UP',
        'floor' => 'DOWN',
        'HALF_UP' => 'HALF_UP',
        'HALF_EVEN' => 'HALF_EVEN',
        'DOWN' => 'DOWN',
        'UP' => 'UP',
    ];

    public function show(Store $store): JsonResponse
    {
        $this->authorizeStore($store);

        $settings = $store->taxSetting ?? StoreTaxSetting::create([
            'store_id' => $store->id,
            'enable_tax' => false,
            'tax_name' => 'IVA',
            'tax_rate' => 0.19,
            'prices_include_tax' => false,
            'tax_rounding_mode' => 'round',
        ]);

        return response()->json([
            'message' => 'Configuracion de IVA',
            'data' => $this->formatSettings($settings),
        ]);
    }

    public function update(UpdateTaxSettingRequest $request, Store $store): JsonResponse
    {
        $this->authorizeStore($store);

        $validated = $request->validated();
        $payload = [];

        if (array_key_exists('enable_tax', $validated)) {
            $payload['enable_tax'] = (bool) $validated['enable_tax'];
        }

        if (array_key_exists('tax_name', $validated)) {
            $payload['tax_name'] = $validated['tax_name'] ?: 'IVA';
        }

        if (array_key_exists('prices_include_tax', $validated)) {
            $payload['prices_include_tax'] = (bool) $validated['prices_include_tax'];
        }

        if ($request->normalizedTaxRate() !== null) {
            $payload['tax_rate'] = $request->normalizedTaxRate();
        }

        if (array_key_exists('tax_rounding_mode', $validated) && $validated['tax_rounding_mode']) {
            $payload['tax_rounding_mode'] = $this->mapUiRoundingToDb((string) $validated['tax_rounding_mode']);
        }

        $settings = StoreTaxSetting::updateOrCreate(
            ['store_id' => $store->id],
            $payload
        );

        $exampleInput = 100000.0;
        $preview = $settings->calculateTax($exampleInput);

        return response()->json([
            'message' => 'Configuracion de IVA actualizada correctamente.',
            'data' => $this->formatSettings($settings),
            'meta' => [
                'preview' => [
                    'example_input' => round($exampleInput, 2),
                    'base_sin_iva' => round((float) $preview['base'], 2),
                    'iva' => round((float) $preview['tax'], 2),
                    'total' => round((float) $preview['total'], 2),
                ],
            ],
            'preview' => [
                'example_input' => round($exampleInput, 2),
                'base_sin_iva' => round((float) $preview['base'], 2),
                'iva' => round((float) $preview['tax'], 2),
                'total' => round((float) $preview['total'], 2),
            ],
        ]);
    }

    private function formatSettings(StoreTaxSetting $settings): array
    {
        $rate = (float) $settings->tax_rate;

        return [
            'id' => $settings->id,
            'store_id' => $settings->store_id,
            'enable_tax' => (bool) $settings->enable_tax,
            'tax_name' => $settings->tax_name ?: 'IVA',
            'tax_rate' => round($rate, 6),
            'tax_rate_percent' => round($rate * 100, 4),
            'prices_include_tax' => (bool) $settings->prices_include_tax,
            'tax_rounding_mode' => $this->mapDbRoundingToUi((string) $settings->tax_rounding_mode),
        ];
    }

    private function mapUiRoundingToDb(string $mode): string
    {
        $normalized = strtoupper(trim($mode));
        return self::UI_TO_DB_ROUNDING[$normalized] ?? 'round';
    }

    private function mapDbRoundingToUi(string $mode): string
    {
        $normalized = trim($mode);
        return self::DB_TO_UI_ROUNDING[$normalized] ?? 'HALF_UP';
    }

    private function authorizeStore(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para acceder a esta tienda.');
        }
    }
}
