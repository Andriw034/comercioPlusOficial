<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpleReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'order_id',
        'receipt_number',
        'receipt_date',
        'total',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total'        => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
