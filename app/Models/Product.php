<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 691c95be (comentario)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
<<<<<<< HEAD
    use HasFactory;
=======

   protected $fillable = [
  'description',
        'price',
        'stock',
        'image',
        'offer',
        'average_ating',
    ];

    protected $allowIncluded = ['user'];

    protected $allowSort =  [
      'description',
        'price',
        'stock',
        'image',
        'offer',
          'average_ating',

    ];
    protected $allowFilter  = [
        'description',
        'price',
        'stock',
        'image',
        'offer',
          'average_ating',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
>>>>>>> 691c95be (comentario)

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
        'offer',
        'average_rating',
        'user_id',
        'store_id',
    ];

<<<<<<< HEAD
    public function category() { return $this->belongsTo(Category::class); }
    public function store()    { return $this->belongsTo(Store::class); }
=======
    public function ratings()
{
    return $this->hasMany(Rating::class);
}

 
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderproduct()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function cartproducts()
    {
        return $this->hasMany(CartProduct::class);
    }
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

>>>>>>> 691c95be (comentario)
}
