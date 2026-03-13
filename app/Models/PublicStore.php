<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'name',
        'nombre_tienda',
        'slug',
        'descripcion',
        'logo',
        'cover',
        'direccion',
        'telefono',
        'estado',
        'horario_atencion',
        'categoria_principal',
        'calificacion_promedio'
    ];

    protected $casts = [
        'calificacion_promedio' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
