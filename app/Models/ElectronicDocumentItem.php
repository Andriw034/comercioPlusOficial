<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectronicDocumentItem extends Model
{
    protected $fillable = [
        'electronic_document_id',
        'product_id',
        'line_number',
        'code',
        'description',
        'unit_measure',
        'quantity',
        'unit_price',
        'discount',
        'tax_amount',
        'line_total',
        'tax_type',
        'tax_rate',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_number' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ElectronicDocument::class, 'electronic_document_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
