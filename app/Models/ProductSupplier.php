<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_name',
        'supplier_phone',
        'purchase_price',
        'delivery_days',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'delivery_days'  => 'integer',
        'is_primary'     => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
