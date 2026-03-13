<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoRestockSetting extends Model
{
    protected $fillable = [
        'store_id',
        'enabled',
        'min_stock_threshold',
        'days_of_stock_target',
        'frequency',
        'auto_approve',
        'excluded_product_ids',
        'supplier_email',
        'supplier_whatsapp',
        'last_generated_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_approve' => 'boolean',
        'excluded_product_ids' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isProductExcluded(int $productId): bool
    {
        return in_array($productId, $this->excluded_product_ids ?? [], true);
    }

    public function excludeProduct(int $productId): void
    {
        $excluded = $this->excluded_product_ids ?? [];

        if (! in_array($productId, $excluded, true)) {
            $excluded[] = $productId;
            $this->update(['excluded_product_ids' => array_values($excluded)]);
        }
    }

    public function includeProduct(int $productId): void
    {
        $excluded = $this->excluded_product_ids ?? [];

        $this->update([
            'excluded_product_ids' => array_values(array_diff($excluded, [$productId])),
        ]);
    }
}
