<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'store_id',
        'product_id',
        'type',
        'quantity',
        'stock_after',
        'unit_cost',
        'unit_price',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public const TYPE_SALE = 'sale';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RETURN = 'return';
    public const TYPE_CANCEL = 'cancel';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
    }
}
