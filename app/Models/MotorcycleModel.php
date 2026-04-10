<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorcycleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'year_from',
        'year_to',
        'engine_cc',
        'type',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to'   => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_motorcycle_compatibility');
    }

    public function coversYear(int $year): bool
    {
        return $year >= $this->year_from && ($this->year_to === null || $year <= $this->year_to);
    }
}
