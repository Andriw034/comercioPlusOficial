<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpsertRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'min:3', Rule::unique('stores', 'slug')->ignore($this->route('store')?->id)],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'min:5'],
            'phone' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string'],
            'main_category' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
