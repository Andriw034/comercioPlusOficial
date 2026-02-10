<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'theme',
        'logo_path',
        'cover_path',
        'background_path',
        'logo_url',
        'cover_url',
        'background_url',
        'logo_public_id',
        'cover_public_id',
        'slug',
        'phone',
        'whatsapp',
        'support_email',
        'facebook',
        'instagram',
        'address',
        'is_visible'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
