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
        $path = $this->image_path;

        if (!$path) {
            return asset('images/no-image.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage')) {
            return $path;
        }

        if (str_starts_with($path, 'storage')) {
            return '/'.$path;
        }

        // products/{store}/{file} -> /storage/products/{store}/{file}
        return Storage::url($path);
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
