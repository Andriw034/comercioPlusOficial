<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',         // <-- importante para el scope por tienda
        'user_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',            // columna real en tu BD
        'offer',
        'average_rating',
    ];

    /* ===================== Relaciones ===================== */

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // Si tu modelo es OrderProduct y la tabla es order_products:
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function cartProducts()
    {
        return $this->hasMany(CartProduct::class);
    }

    /* ===================== Listas permitidas ===================== */

    protected $allowIncluded = [
        'store', 'user', 'category', 'sales', 'ratings', 'orderProducts', 'cartProducts'
    ];

    protected $allowSort = [
        'id', 'name', 'description', 'price', 'stock', 'image',
        'category_id', 'offer', 'average_rating', 'user_id', 'store_id',
        'created_at'
    ];

    protected $allowFilter = [
        'name', 'description', 'price', 'stock', 'image',
        'category_id', 'offer', 'average_rating', 'user_id', 'store_id',
    ];

    /* ===================== Scopes utilitarios ===================== */

    // Incluir relaciones vía ?included=...
    public function scopeIncluded(Builder $query)
    {
        $included = request('included');
        if (empty($this->allowIncluded) || empty($included)) {
            return $query;
        }

        $relations = array_filter(explode(',', $included));
        $valid = array_values(array_intersect($relations, $this->allowIncluded));

        return $query->with($valid);
    }

    // Filtrar por ?filter[campo]=valor (exacto para numéricos)
    public function scopeFilter(Builder $query)
    {
        $filters = request('filter');
        if (empty($this->allowFilter) || empty($filters) || !is_array($filters)) {
            return $query;
        }

        foreach ($filters as $field => $value) {
            if (!in_array($field, $this->allowFilter, true) || $value === '') {
                continue;
            }

            // Campos numéricos → match exacto
            if (in_array($field, ['user_id', 'store_id', 'category_id', 'stock', 'price'], true)) {
                $query->where($field, $value);
            } else {
                $query->where($field, 'LIKE', '%' . $value . '%');
            }
        }

        return $query;
    }

    // Paginación opcional vía ?perPage=...
    public function scopeGetOrPaginate(Builder $query)
    {
        $perPage = intval(request('perPage', 0));
        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    // Ordenamiento dinámico vía ?sort=-created_at
    public function scopeSort(Builder $query)
    {
        $sort = request('sort');
        if (empty($this->allowSort) || empty($sort)) {
            return $query;
        }

        $fields = array_filter(explode(',', $sort));
        foreach ($fields as $field) {
            $direction = 'asc';
            if (\Illuminate\Support\Str::startsWith($field, '-')) {
                $direction = 'desc';
                $field = ltrim($field, '-');
            }
            if (in_array($field, $this->allowSort, true)) {
                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }

    // Limitar por tienda específica
    public function scopeForStore(Builder $query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // Limitar a la tienda del usuario autenticado (si existe)
    public function scopeOwnedByAuth(Builder $query)
    {
        $store = Auth::user()?->store;
        return $store ? $query->where('store_id', $store->id) : $query->whereRaw('1=0');
    }
}
