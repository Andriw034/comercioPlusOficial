<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrediction extends Model
{
    protected $fillable = [
        'product_id',
        'current_stock',
        'avg_daily_sales',
        'predicted_days_until_depletion',
        'predicted_depletion_date',
        'recommended_restock_quantity',
        'calculation_details',
    ];

    protected $casts = [
        'predicted_depletion_date' => 'date',
        'calculation_details' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
