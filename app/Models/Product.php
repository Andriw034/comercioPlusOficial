<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'store_id', 'user_id', 'stock', 'status', 'slug', 'is_promo', 'promo_price', 'image_path', 'image_url', 'image'];

    protected $allowIncluded = ['store', 'category', 'ratings'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

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
}
