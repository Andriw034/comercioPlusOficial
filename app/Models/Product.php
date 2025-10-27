<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    // Ajusta si tienes otros campos; estos son los comunes en tu proyecto
    protected $fillable = [
        'store_id',
        'user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'status',       // tinyint 0/1
        'image_path',   // ruta relativa en disco "public"
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'int',
        'status' => 'int',
    ];

    // Para que se serialicen automáticamente
    protected $appends = [
        'image_url',
        'price_formatted',
    ];

    /* ----------------- Accessors ----------------- */

    /**
     * URL pública normalizada de la imagen.
     * Admite:
     *  - http(s)://...
     *  - /storage/...
     *  - storage/...
     *  - products/{store}/{file}
     *  - null => imagen por defecto
     */
  public function getImageUrlAttribute(): string
{
    // Fallback si no hay imagen
    if (!$this->image_path) {
        // Asegúrate de tener este archivo en public/images/no-image.png
        return asset('images/no-image.png') . '?v=' . ($this->updated_at?->timestamp ?? time());
    }

    $path = $this->image_path;

    // Si ya es URL absoluta
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path . (str_contains($path, '?') ? '' : ('?v=' . ($this->updated_at?->timestamp ?? time())));
    }

    // Normaliza rutas que vienen como "/storage..." o "storage..."
    if (str_starts_with($path, '/storage')) {
        $url = url($path);
    } elseif (str_starts_with($path, 'storage')) {
        $url = url('/' . ltrim($path, '/'));
    } else {
        // Ej: "stores/{store_id}/products/{product_id}/main.jpg"
        $url = \Storage::disk('public')->url($path);
    }

    return $url . (str_contains($url, '?') ? '' : ('?v=' . ($this->updated_at?->timestamp ?? time())));
}

    public function getPriceFormattedAttribute(): string
    {
        return '$'.number_format((float) $this->price, 0, ',', '.');
    }

    /* ----------------- Scopes útiles ----------------- */

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    public function scopeFromVisibleStores($q)
    {
        return $q->whereHas('store', fn ($qq) => $qq->where('is_visible', true));
    }

    /* ----------------- Relaciones ----------------- */

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
