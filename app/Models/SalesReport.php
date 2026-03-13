<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReport extends Model
{
    protected $table = 'sales_reports';

    protected $fillable = [
        'store_id',
        'range_type',
        'start_date',
        'end_date',
        'period_label',
        'currency',
        'totals_json',
        'generated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'totals_json' => 'array',
        'generated_at' => 'datetime',
    ];

    public const RANGE_WEEKLY = 'weekly';
    public const RANGE_MONTHLY = 'monthly';
    public const RANGE_YEARLY = 'yearly';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('range_type', $type);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('start_date');
    }

    public function getVentasBrutasAttribute(): float
    {
        return (float) ($this->totals_json['ventas_brutas'] ?? 0);
    }

    public function getIvaCobradoAttribute(): float
    {
        return (float) ($this->totals_json['iva_cobrado'] ?? 0);
    }

    public function getVentasNetasAttribute(): float
    {
        return (float) ($this->totals_json['ventas_netas'] ?? 0);
    }

    public function getCogsAttribute(): float
    {
        return (float) ($this->totals_json['cogs'] ?? 0);
    }

    public function getUtilidadBrutaAttribute(): float
    {
        return (float) ($this->totals_json['utilidad_bruta'] ?? 0);
    }

    public function getUtilidadNetaAttribute(): float
    {
        return (float) ($this->totals_json['utilidad_neta'] ?? 0);
    }

    public function getMargenBrutoPctAttribute(): float
    {
        return (float) ($this->totals_json['margen_bruto_pct'] ?? 0);
    }

    public function getOrdersCountAttribute(): int
    {
        return (int) ($this->totals_json['orders_count'] ?? 0);
    }

    public function getTopProductsAttribute(): array
    {
        return (array) ($this->totals_json['top_products'] ?? []);
    }
}
