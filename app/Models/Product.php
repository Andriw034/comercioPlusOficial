<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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

    // Listas de control para scopes dinámicos
    protected $allowIncluded = ['category', 'store', 'ratings', 'orderproduct', 'cartproducts', 'user'];
    protected $allowSort = ['name', 'price', 'stock', 'average_rating'];
    protected $allowFilter = ['name', 'description', 'price', 'stock', 'offer', 'average_rating'];

    // Relaciones
    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function store() { return $this->belongsTo(Store::class); }
    public function ratings() { return $this->hasMany(Rating::class); }
    public function orderproduct() { return $this->hasMany(OrderProduct::class); }
    public function cartproducts() { return $this->hasMany(CartProduct::class); }

    // Scopes utilitarios
    public function scopeIncluded(Builder $query)
    {
        if (empty($this->allowIncluded) || empty(request('included'))) {
            return $query;
        }
        $relations = explode(',', request('included'));
        $allowIncluded = collect($this->allowIncluded);
        foreach ($relations as $key => $relationship) {
            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        return $query->with($relations);
    }

    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return $query;
        }
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
        foreach ($filters as $filter => $value) {
            if ($allowFilter->contains($filter)) {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
        return $query;
    }

    public function scopeSort(Builder $query)
    {
        $sort = request('sort');
        if (!$sort) return $query;
        $allowSort = collect($this->allowSort);
        $direction = 'asc';
        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $sort = ltrim($sort, '-');
        }
        if ($allowSort->contains($sort)) {
            return $query->orderBy($sort, $direction);
        }
        return $query;
    }

    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));
            if ($perPage) {
                return $query->paginate($perPage);
            }
        }
        return $query->get();
    }
}

