<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'next_product_barcode',
    ];

    protected $casts = [
        'next_product_barcode' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
