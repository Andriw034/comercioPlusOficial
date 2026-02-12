<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;
    
     protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $allowIncluded = ['user'];

      protected $allowSort =  [
        'name',
        'slug',
        'description',
        'is_active',
    ];

      protected $allowFilter  =  [
        'name',
        'slug',
        'description',
        'is_active',
    ];





    public function scopeIncluded(Builder $query) // Scope local que permite incluir relaciones dinÃ¡micamente
    {
        if (empty($this->allowIncluded) || empty(request('included'))) { // Si no hay relaciones permitidas o no se solicitÃ³ ninguna
            return $query; // Retorna la consulta sin modificar
        }

        $relations = explode(',', request('included')); // Convierte el string ?included=... en un array (por comas)
        $allowIncluded = collect($this->allowIncluded); // Convierte la lista de relaciones permitidas en una colecciÃ³n

        foreach ($relations as $key => $relationship) { // Recorre cada relaciÃ³n pedida por el usuario
            if (!$allowIncluded->contains($relationship)) { // Si esa relaciÃ³n no estÃ¡ permitida
                unset($relations[$key]); // Se elimina del array para no ser incluida
            }
        }

        return $query->with($relations); // Incluye solo las relaciones vÃ¡lidas en la consulta
    }



    public function scopeFilter(Builder $query) // Scope local que permite aplicar filtros desde la URL (?filter[...]=...)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) { // Si no hay filtros permitidos o no se enviÃ³ ninguno
            return $query; // Retorna la consulta sin modificar
        }

        $filters = request('filter'); // Obtiene todos los filtros enviados desde la URL
        $allowFilter = collect($this->allowFilter); // Convierte los campos permitidos en colecciÃ³n Laravel

        foreach ($filters as $filter => $value) { // Recorre cada filtro recibido (ej: name => 'HP')
            if ($allowFilter->contains($filter)) { // Si el filtro es uno de los permitidos
                $query->where($filter, 'LIKE', '%' . $value . '%'); // Aplica bÃºsqueda parcial (LIKE '%valor%')
            }
        }

        return $query; // Retorna la consulta modificada con los filtros aplicados
    }


    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));

            if ($perPage) {
                return $query->paginate($perPage); // Devuelve con paginaciÃ³n
            }
        }

        return $query->get(); // Devuelve todos si no hay perPage
    }
}