<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cart extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     protected $fillable = [
        'user_id',
        'status',
    ];

    protected $allowIncluded = ['user'];

    protected $allowSort = [
        'user_id',
        'status',
    ];

    protected $allowFilter = [
        'user_id',
        'status',
    ];

     public function scopeIncluded(Builder $query) // Scope local que permite incluir relaciones dinámicamente
    {
        if (empty($this->allowIncluded) || empty(request('included'))) { // Si no hay relaciones permitidas o no se solicitó ninguna
            return $query; // Retorna la consulta sin modificar
        }

        $relations = explode(',', request('included')); // Convierte el string ?included=... en un array (por comas)
        $allowIncluded = collect($this->allowIncluded); // Convierte la lista de relaciones permitidas en una colección

        foreach ($relations as $key => $relationship) { // Recorre cada relación pedida por el usuario
            if (!$allowIncluded->contains($relationship)) { // Si esa relación no está permitida
                unset($relations[$key]); // Se elimina del array para no ser incluida
            }
        }

        return $query->with($relations); // Incluye solo las relaciones válidas en la consulta
    }



    public function scopeFilter(Builder $query) // Scope local que permite aplicar filtros desde la URL (?filter[...]=...)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) { // Si no hay filtros permitidos o no se envió ninguno
            return $query; // Retorna la consulta sin modificar
        }

        $filters = request('filter'); // Obtiene todos los filtros enviados desde la URL
        $allowFilter = collect($this->allowFilter); // Convierte los campos permitidos en colección Laravel

        foreach ($filters as $filter => $value) { // Recorre cada filtro recibido (ej: name => 'HP')
            if ($allowFilter->contains($filter)) { // Si el filtro es uno de los permitidos
                $query->where($filter, 'LIKE', '%' . $value . '%'); // Aplica búsqueda parcial (LIKE '%valor%')
            }
        }

        return $query; // Retorna la consulta modificada con los filtros aplicados
    }


    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));

            if ($perPage) {
                return $query->paginate($perPage); // Devuelve con paginación
            }
        }

        return $query->get(); // Devuelve todos si no hay perPage
    }

}
