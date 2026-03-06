<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMetricCache extends Model
{
    protected $table = 'ai_metrics_cache';

    protected $fillable = [
        'store_id',
        'metric_type',
        'metric_data',
        'calculated_at',
        'expires_at',
    ];

    protected $casts = [
        'metric_data' => 'array',
        'calculated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
