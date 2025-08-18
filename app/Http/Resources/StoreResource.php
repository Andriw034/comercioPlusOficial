<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'descripcion' => $this->descripcion,
            'logo' => $this->logo,
            'cover' => $this->cover,
            'cover_image' => $this->cover_image,
            'background' => $this->background,
            'primary_color' => $this->primary_color,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
            'button_color' => $this->button_color,
            'custom_css' => $this->custom_css,
            'social_links' => $this->social_links ? json_decode($this->social_links) : null,
            'contact_info' => $this->contact_info ? json_decode($this->contact_info) : null,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'horario_atencion' => $this->horario_atencion,
            'categoria_principal' => $this->categoria_principal,
            'calificacion_promedio' => $this->calificacion_promedio,
            'is_active' => $this->is_active,
            'estado' => $this->estado,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'products_count' => $this->whenCounted('products'),
            'public_store' => $this->whenLoaded('publicStore'),
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image,
                    ];
                });
            }),
        ];
    }
}
