<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * Asignación masiva permitida
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'store_id',
        'short_description',
        'is_popular',
        'popularity',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_popular' => 'boolean',
        'popularity' => 'integer',
        'parent_id'  => 'integer',
        'store_id'   => 'integer',
    ];

    /**
     * Valores por defecto
     */
    protected $attributes = [
        'is_popular' => false,
        'popularity' => 0,
    ];

    /**
     * Listas de control para scopes
     */
    protected $allowIncluded = ['products', 'parent', 'children'];
    protected $allowSort     = ['name', 'slug'];
    protected $allowFilter   = ['name', 'slug', 'description'];

    /* ============================
       Relaciones
       ============================ */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /* ============================
       Boot: slug opcional y único por tienda
       ============================ */

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            // Si no envían slug, generarlo a partir del nombre
            if (empty($category->slug) && !empty($category->name)) {
                $base = Str::slug($category->name);
                $slug = $base;
                $i = 1;

                // Unicidad por tienda
                while (static::query()
                    ->where('store_id', $category->store_id)
                    ->where('slug', $slug)
                    ->when($category->exists, fn ($q) => $q->where('id', '!=', $category->id))
                    ->exists()
                ) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $category->slug = $slug;
            }

            // Valores defensivos
            if ($category->popularity === null) {
                $category->popularity = 0;
            }
            if ($category->is_popular === null) {
                $category->is_popular = false;
            }
        });
    }

    /* ============================
       Scopes útiles
       ============================ */

    /**
     * Limitar por tienda
     */
    public function scopeOfStore(Builder $query, int|string|null $storeId): Builder
    {
        return $query->when($storeId, fn ($q) => $q->where('store_id', $storeId));
    }

    /**
     * Incluir relaciones permitidas vía ?included=...
     */
    public function scopeIncluded(Builder $query): Builder
    {
        $included = request('included');
        if (empty($this->allowIncluded) || empty($included)) {
            return $query;
        }

        $relations = collect(explode(',', (string) $included))
            ->map(fn ($r) => trim($r))
            ->filter(fn ($r) => $r !== '' && in_array($r, $this->allowIncluded, true))
            ->values()
            ->all();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Filtros básicos vía ?filter[name]=...&filter[slug]=...
     */
    public function scopeFilter(Builder $query): Builder
    {
        $filters = request('filter');
        if (empty($this->allowFilter) || empty($filters) || !is_array($filters)) {
            return $query;
        }

        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') continue;
            if (in_array($field, $this->allowFilter, true)) {
                $query->where($field, 'LIKE', '%' . $value . '%');
            }
        }

        return $query;
    }

    /**
     * Paginación opcional vía ?perPage=...
     */
    public function scopeGetOrPaginate(Builder $query)
    {
        $perPage = (int) request('perPage', 0);

        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
}
