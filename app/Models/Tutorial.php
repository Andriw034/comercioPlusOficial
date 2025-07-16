<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

        protected $allowFilter = ['language', 'content'];

    protected $allowSort = ['language', 'content'];

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
