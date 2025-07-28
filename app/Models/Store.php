<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'logo', 'cover', 'user_id', 'color', 'theme', 'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function publicStore() {
        return $this->hasOne(PublicStore::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}
