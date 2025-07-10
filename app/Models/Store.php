<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'email',
        'direccion',
        'telefono',
        'categoria_principal',
        'primary_color',
        'text_color',
        'button_color',
        'background_color',
        'logo',
        'cover_image',
        'estado',
        'calificacion_promedio',
        'theme'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'theme' => 'array',
        'calificacion_promedio' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function getCoverUrlAttribute()
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }
}
