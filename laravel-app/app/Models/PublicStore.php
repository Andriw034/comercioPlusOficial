<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicStore extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'nombre_tienda',
        'business_name',
        'ruc',
        'business_type',
        'slug',
        'descripcion',
        'business_description',
        'short_description',
        'tags',
        'logo',
        'cover',
        'direccion',
        'latitude',
        'longitude',
        'google_maps_url',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'youtube_url',
        'website_url',
        'email_contacto',
        'whatsapp_number',
        'telefono',
        'estado',
        'horario_atencion',
        'categoria_principal',
        'calificacion_promedio',
        'store_id',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'calificacion_promedio' => 'decimal:2',
        'tags' => 'array',
    ];

    /**
     * Obtener el usuario propietario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la tienda interna.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
