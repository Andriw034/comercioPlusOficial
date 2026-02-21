<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $table = 'purchase_request_items';

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'current_stock',
        'suggested_qty',
        'ordered_qty',
        'last_cost',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'suggested_qty' => 'integer',
        'ordered_qty' => 'integer',
        'last_cost' => 'decimal:2',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEstimatedCostAttribute(): float
    {
        return round(((float) $this->last_cost) * (int) $this->ordered_qty, 2);
    }
}
