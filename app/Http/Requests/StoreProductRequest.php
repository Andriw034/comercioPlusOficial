<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Debe estar autenticado y tener una tienda
        return auth()->check() && auth()->user()->store !== null;
    }

    public function rules(): array
    {
        $store = auth()->user()->store;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            // La categoría debe pertenecer a la tienda del usuario
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('store_id', $store->id)),
            ],
            // Tu BD usa columna "image" (no image_path)
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
