<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPickingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_product_id',
        'product_id',
        'user_id',
        'mode',
        'action',
        'code',
        'qty',
        'error_code',
        'message',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public const MODE_SCANNER = 'scanner';
    public const MODE_MANUAL = 'manual';
    public const MODE_SYSTEM = 'system';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

