<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_requests';

    protected $fillable = [
        'store_id',
        'status',
        'period_tag',
        'notes',
        'expected_date',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'received_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_SENT => 'Enviada al proveedor',
            self::STATUS_RECEIVED => 'Recibida',
            self::STATUS_CANCELLED => 'Cancelada',
            default => $this->status,
        };
    }

    public function getTotalEstimatedAttribute(): float
    {
        return $this->items->sum(fn ($item) => ((float) $item->last_cost) * (int) $item->ordered_qty);
    }
}
