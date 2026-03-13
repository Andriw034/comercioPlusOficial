<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $store = Auth::user()->stores()->firstOrFail();
        $id    = $this->route('category'); // Category ID in route model binding

        return [
            'name' => [
                'required','string','max:80',
                Rule::unique('categories','name')
                    ->where('store_id', $store->id)
                    ->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una categoría con ese nombre en tu tienda.',
        ];
    }
}
