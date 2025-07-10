<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Fix typo in method name
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Parent category relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Children categories relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
