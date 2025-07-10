<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function ratings()
{
    return $this->hasMany(Rating::class);
}

 //-----------------------------------------------------

 
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderproduct()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function cartproducts()
    {
        return $this->hasMany(CartProduct::class);
    }
}
