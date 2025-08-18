<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Muy importante para Spatie
    protected $guard_name = 'web';

    protected $fillable = [
        'name','email','password','phone','avatar','status','address','role'
    ];

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // === SCOPES ===

    // Incluir relaciones dinámicas desde ?included=store,products
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
