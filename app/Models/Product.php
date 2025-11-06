<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_path',   // 👈 solo esta (no dupliques)
        'category_id',
        'store_id',
        'user_id',
        'status',
        'offer',
        'average_rating',
    ];

    // Si quieres que aparezca en JSON automáticamente:
    // protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        // 1) URL externa completa
        if (!empty($this->image_path) && preg_match('#^https?://#i', $this->image_path)) {
            return $this->image_path;
        }

        // 2) Ruta en el disco 'public', ej: "products/archivo.jpg"
        if (!empty($this->image_path)) {
            $path = preg_replace('#^public/#', '', $this->image_path);
            return Storage::disk('public')->url($path);
        }

        // 3) Nada definido
        return null;
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }
}
