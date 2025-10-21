<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'cover_path',
        'logo_url',
        'background_path',
        'background_url',
        'theme_primary',
        'visits',
        'phone',
        'whatsapp',
        'support_email',
        'address',
        'city',
        'is_visible',
        'payment_instructions',
        'shipping_radius_km',
        'shipping_base_cost',
        'tax_percent',
        'price_includes_tax',
        'notify_email',
    ];

    protected $casts = [
        'visits' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    public function getCoverUrlAttribute()
    {
        return $this->cover_path
            ? Storage::disk('public')->url($this->cover_path)
            : null;
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
