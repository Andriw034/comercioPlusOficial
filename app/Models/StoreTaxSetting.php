<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTaxSetting extends Model
{
    protected $table = 'store_tax_settings';

    protected $fillable = [
        'store_id',
        'enable_tax',
        'tax_name',
        'tax_rate',
        'prices_include_tax',
        'tax_rounding_mode',
    ];

    protected $casts = [
        'enable_tax' => 'boolean',
        'tax_rate' => 'decimal:4',
        'prices_include_tax' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function calculateTax(float $price): array
    {
        if (! $this->enable_tax || $this->tax_rate <= 0) {
            return ['base' => $price, 'tax' => 0.0, 'total' => $price];
        }

        if ($this->prices_include_tax) {
            $base = $price / (1 + (float) $this->tax_rate);
            $tax = $price - $base;
        } else {
            $base = $price;
            $tax = $price * (float) $this->tax_rate;
        }

        $tax = $this->applyRounding($tax);
        $total = $base + $tax;

        return [
            'base' => round($base, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }

    private function applyRounding(float $value): float
    {
        return match ($this->tax_rounding_mode) {
            'ceil' => ceil($value * 100) / 100,
            'floor' => floor($value * 100) / 100,
            default => round($value, 2),
        };
    }
}
