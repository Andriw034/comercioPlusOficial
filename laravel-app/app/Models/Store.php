<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
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
        'description',
        'slug',
        'logo',
        'logo_path',
        'cover_path',
        'cover_image',
        'background_color',
        'text_color',
        'button_color',
        'primary_color',
        'descripcion',
        'direccion',
        'telefono',
        'estado',
        'horario_atencion',
        'categoria_principal',
        'calificacion_promedio',
        'theme',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'calificacion_promedio' => 'decimal:2',
        'theme' => 'array',
    ];

    /**
     * Obtener el usuario propietario de la tienda.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener los productos de la tienda.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtener las categorías de la tienda.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Obtener las órdenes de la tienda.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtener la tienda pública.
     */
    public function publicStore(): HasOne
    {
        return $this->hasOne(PublicStore::class);
    }

    /**
     * Obtener los temas de la tienda.
     */
    public function themes(): HasMany
    {
        return $this->hasMany(StoreTheme::class);
    }
}
