<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandingThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shopName' => ['required','string','min:3','max:100'],
            'logo'     => ['required','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'cover'    => ['required','image','mimes:jpg,jpeg,png,webp','max:8192'],
        ];
    }
}
