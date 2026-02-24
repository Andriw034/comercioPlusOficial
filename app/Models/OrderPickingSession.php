<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPickingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'scan_consecutive_failures',
        'fallback_required',
        'last_error_code',
        'last_code',
    ];

    protected $casts = [
        'scan_consecutive_failures' => 'integer',
        'fallback_required' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

