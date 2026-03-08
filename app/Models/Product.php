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
        'sku',
        'brand',
        'description',
        'price',
        'category_id',
        'store_id',
        'user_id',
        'stock',
        'unit',
        'ref_adicional',
        'status',
        'slug',
        'is_promo',
        'promo_price',
        'image_path',
        'image_url',
        'image',
        'cost_price',
        'sale_price',
        'reorder_point',
        'allow_backorder',
        'metadata',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'reorder_point' => 'integer',
        'allow_backorder' => 'boolean',
        'metadata' => 'array',
    ];

    protected $allowIncluded = ['store', 'category', 'ratings', 'productCodes'];

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

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function productCodes()
    {
        return $this->hasMany(ProductCode::class);
    }

    public function stockPredictions()
    {
        return $this->hasMany(StockPrediction::class);
    }

    public function suppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    // Alias corto para consumo en APIs futuras.
    public function codes()
    {
        return $this->productCodes();
    }

    public function needsReorder(): bool
    {
        return (int) $this->stock <= (int) $this->reorder_point;
    }

    public function getPriceWithIvaAttribute(): float
    {
        $base = (float) ($this->sale_price ?? $this->price ?? 0);
        return round($base * 1.19, 2);
    }

    public function getTotalCostAttribute(): float
    {
        return round(max(0, (int) $this->stock) * (float) ($this->cost_price ?? 0), 2);
    }

    public function getTotalSaleAttribute(): float
    {
        $sale = (float) ($this->sale_price ?? $this->price ?? 0);
        return round(max(0, (int) $this->stock) * $sale, 2);
    }

    public function getTotalSaleWithIvaAttribute(): float
    {
        return round($this->total_sale * 1.19, 2);
    }

    public function getStockStatusAttribute(): string
    {
        $stock = (int) $this->stock;
        $min = max(0, (int) $this->reorder_point);

        if ($stock <= 0) {
            return 'agotado';
        }

        if ($stock < $min) {
            return 'bajo';
        }

        return 'normal';
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    public function setMeta(string $key, mixed $value): self
    {
        $this->metadata = array_merge($this->metadata ?? [], [$key => $value]);
        return $this;
    }

    public function setMetas(array $data): self
    {
        $this->metadata = array_merge($this->metadata ?? [], $data);
        return $this;
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
