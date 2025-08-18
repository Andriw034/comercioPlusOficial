<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user  = auth()->user();
        $store = $user?->store;
        $product = $this->route('product'); // Product inyectado por la ruta

        // Debe tener tienda y el producto debe pertenecer a su tienda
        return $user && $store && $product && $product->store_id === $store->id;
    }

    public function rules(): array
    {
        $store = auth()->user()->store;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('store_id', $store->id)),
            ],
            'image'       => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no pertenece a tu tienda.',
        ];
    }
}
