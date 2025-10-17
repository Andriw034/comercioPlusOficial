<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'image_path',
        'category_id',
        'offer',
        'average_rating',
        'user_id',
        'store_id',
        'status',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'stock'          => 'integer',
        'offer'          => 'boolean',
        'average_rating' => 'decimal:2',
        'user_id'        => 'integer',
        'store_id'       => 'integer',
        'category_id'    => 'integer',
        'status'         => 'boolean',
    ];

    // ===== Relaciones =====
    public function user()         { return $this->belongsTo(User::class); }
    public function category()     { return $this->belongsTo(Category::class); }
    public function store()        { return $this->belongsTo(Store::class); }
    public function ratings()      { return $this->hasMany(Rating::class); }
    public function orderproduct() { return $this->hasMany(OrderProduct::class); }
    public function cartproducts() { return $this->hasMany(CartProduct::class); }

    // ===== Accessor de URL de imagen =====
    public function getImageUrlAttribute(): string
    {
        // Prioriza el campo 'image' (tu controlador guarda aquí)
        $path = $this->image ?: $this->image_path;

        if (!empty($path)) {
            // Construye URL pública estable para artisan serve
            return asset('storage/'.$path);
        }

        // Placeholder local si no hay imagen
        return asset('images/placeholder.png');
    }

    // ===== Slug único por tienda (auto) =====
    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            if (empty($p->slug) && !empty($p->name)) {
                $base = Str::slug($p->name);
                $slug = $base;
                $i = 1;

                while (static::query()
                    ->where('store_id', $p->store_id)
                    ->where('slug', $slug)
                    ->when($p->exists, fn ($q) => $q->where('id', '!=', $p->id))
                    ->exists()
                ) {
                    $slug = $base.'-'.$i;
                    $i++;
                }

                $p->slug = $slug;
            }
        });
    }

    // ===== Scopes utilitarios (tus originales) =====
    protected $allowIncluded = ['category', 'store', 'ratings', 'orderproduct', 'cartproducts', 'user'];
    protected $allowSort     = ['name', 'price', 'stock', 'average_rating'];
    protected $allowFilter   = ['name', 'description', 'price', 'stock', 'offer', 'average_rating'];

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
