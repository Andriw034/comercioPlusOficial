<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.suggested_qty' => ['required', 'integer', 'min:0'],
            'items.*.ordered_qty' => ['required', 'integer', 'min:1'],
            'items.*.last_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
