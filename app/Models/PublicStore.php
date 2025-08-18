<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class PublicStore extends Model
{
    use HasFactory;

    protected $table = 'public_stores';
    protected $allowSort = ['store_name', 'store_status', 'nombre_tienda', 'calificacion_promedio', 'categoria_principal', 'created_at'];
    protected $allowFilter = ['store_status', 'categoria_principal', 'estado'];
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

    /**
     * Relación: una tienda pública pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: una tienda pública está basada en una tienda privada.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // -------------------------------
    // Scopes para API JSON dinámica
    // -------------------------------

    public function scopeIncluded(Builder $query)
    {
        if (request()->has('included')) {
            $relations = explode(',', request('included'));
            $query->with($relations);
        }
    }

    public function scopeFilter(Builder $query)
    {
        if (request()->has('filter')) {
            foreach (request('filter') as $field => $value) {
                if (in_array($field, $this->allowFilter)) {
                    $query->where($field, $value);
                }
            }
        }
    }

    public function scopeSort(Builder $query)
    {
        if (request()->has('sort')) {
            $sortField = request('sort');
            $direction = 'asc';

            if (Str::startsWith($sortField, '-')) {
                $direction = 'desc';
                $sortField = ltrim($sortField, '-');
            }

            $query->orderBy($sortField, $direction);
        }
    }

    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            return $query->paginate((int) request('perPage'));
        }

        return $query->get();
    }
}
