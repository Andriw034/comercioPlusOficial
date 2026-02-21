<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'new_stock' => ['required', 'integer', 'min:0'],
            'note' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
