<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicStore extends Model
{
    use HasFactory;

    protected $table = 'public_stores';

    protected $fillable = [
        'store_name',
        'store_description',
        'store_address',
        'store_phone',
        'store_email',
        'store_image',
        'store_website',
        'store_hours',
        'store_status'
    ];

    // Relación inversa con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
