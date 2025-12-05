<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ Permitimos que cualquier usuario autenticado use este request
        return true;
    }

    public function rules(): array
    {
        $id = $this->user()->id ?? 'NULL';

        return [
            'name'   => ['required','string','max:100'],
            'email'  => ['required','email','max:255',"unique:users,email,{$id}"],
            'phone'  => ['nullable','string','max:30'],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'Formato de correo inválido.',
            'email.unique'   => 'Este correo ya está en uso.',
            'avatar.image'   => 'El archivo debe ser una imagen.',
            'avatar.mimes'   => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'avatar.max'     => 'La imagen no debe superar 2MB.',
        ];
    }
}
