<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
        'offer',
        'average_rating',
        'user_id',
        'store_id',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function store()    { return $this->belongsTo(Store::class); }
}
