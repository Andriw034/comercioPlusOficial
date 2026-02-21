<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enable_tax' => ['sometimes', 'boolean'],
            'tax_name' => ['sometimes', 'nullable', 'string', 'max:50'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'tax_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'prices_include_tax' => ['sometimes', 'boolean'],
            'tax_rounding_mode' => ['sometimes', 'nullable', 'in:HALF_UP,HALF_EVEN,DOWN,UP'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if (array_key_exists('tax_rounding_mode', $input) && is_string($input['tax_rounding_mode'])) {
            $legacyToModern = [
                'round' => 'HALF_UP',
                'ceil' => 'UP',
                'floor' => 'DOWN',
            ];

            $mode = strtoupper(trim($input['tax_rounding_mode']));
            if (isset($legacyToModern[strtolower($mode)])) {
                $input['tax_rounding_mode'] = $legacyToModern[strtolower($mode)];
            } else {
                $input['tax_rounding_mode'] = $mode;
            }
        }

        if (array_key_exists('tax_rate_percent', $input) && $input['tax_rate_percent'] !== null && $input['tax_rate_percent'] !== '') {
            $input['tax_rate'] = ((float) $input['tax_rate_percent']) / 100;
        } elseif (
            (!array_key_exists('tax_rate_percent', $input) || $input['tax_rate_percent'] === null || $input['tax_rate_percent'] === '')
            && array_key_exists('tax_rate', $input)
            && $input['tax_rate'] !== null
            && $input['tax_rate'] !== ''
        ) {
            $input['tax_rate_percent'] = ((float) $input['tax_rate']) * 100;
        }

        $this->replace($input);
    }

    public function normalizedTaxRate(): ?float
    {
        $rate = $this->input('tax_rate');
        if ($rate === null || $rate === '') {
            return null;
        }

        return (float) $rate;
    }
}
