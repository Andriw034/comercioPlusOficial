<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $storeId = auth()->user()->stores()->firstOrFail()->id;

        return [
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
            'status' => ['sometimes','boolean'],

            'category_id' => [
                'required','integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('store_id', $storeId)),
            ],

            // Imagen: 4 MB, formatos comunes
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ];
    }

    /**
     * Normaliza valores antes de validar:
     * - price: quita separadores y castea
     * - stock/status: a tipos correctos
     */
    protected function prepareForValidation(): void
    {
        $price = $this->input('price');

        if (is_string($price)) {
            $normalized = str_replace(['.', ' '], '', $price); // quita miles
            $normalized = str_replace(',', '.', $normalized);  // coma a punto
            $price = $normalized;
        }

        $this->merge([
            'price' => is_numeric($price) ? (float) $price : $price,
            'stock' => is_numeric($this->input('stock')) ? (int) $this->input('stock') : $this->input('stock'),
            'status' => $this->has('status') ? (int) (bool) $this->input('status') : 1,
        ]);
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no pertenece a tu tienda.',
            'image.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'image.max' => 'La imagen no debe superar los 4 MB.',
        ];
    }
}
