<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'user_id',
        'direccion',
        'telefono',
        'estado',
        'horario_atencion',
        'categoria_principal',
        'calificacion_promedio',
        'slug',
        'primary_color',
        'background_color',
        'text_color',
        'button_color',
        'cover_image',
        'background',
        'cover',
        'descripcion',
        'custom_css',
        'social_links',
        'contact_info',
        'is_active',
    ];

    // Relaciones
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function publicStore() {
        return $this->hasOne(PublicStore::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    // === SCOPES ===

    // Incluir relaciones dinámicas desde ?included=products,user
    public function scopeIncluded($query)
    {
        if (request()->filled('included')) {
            $relations = explode(',', request()->input('included'));
            $query->with($relations);
        }
    }

    // Filtro dinámico con ?filter[campo]=valor
    public function scopeFilter($query)
    {
        foreach (request()->query() as $key => $value) {
            if (!in_array($key, ['included', 'sort', 'page', 'per_page'])) {
                $query->where($key, 'LIKE', "%$value%");
            }
        }
    }

    // Ordenamiento dinámico con ?sort=-created_at
    public function scopeSort($query)
    {
        if (request()->filled('sort')) {
            $fields = explode(',', request()->input('sort'));

            foreach ($fields as $field) {
                $direction = 'asc';
                if (Str::startsWith($field, '-')) {
                    $direction = 'desc';
                    $field = ltrim($field, '-');
                }
                $query->orderBy($field, $direction);
            }
        }
    }

    public function scopeGetOrPaginate($query, $perPage)
    {
        if ($perPage) {
            return $query->paginate($perPage);
        }
        return $query->get();
    }
}
