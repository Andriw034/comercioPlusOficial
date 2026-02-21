<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:sent,received,cancelled'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'expected_date' => ['sometimes', 'nullable', 'date'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.ordered_qty' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
