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
        'slug',
        'phone',
        'whatsapp',
        'support_email',
        'facebook',
        'instagram',
        'address',
        'city',
        'is_visible',
        'is_verified',
        'category',
        'schedule',
        'currency',
        'taxes_enabled',
        'payment_methods',
    ];

    protected $casts = [
        'is_visible'      => 'boolean',
        'is_verified'     => 'boolean',
        'taxes_enabled'   => 'boolean',
        'payment_methods' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function taxSetting()
    {
        return $this->hasOne(StoreTaxSetting::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function productCodes()
    {
        return $this->hasMany(ProductCode::class);
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function salesReports()
    {
        return $this->hasMany(SalesReport::class);
    }

    public function autoRestockSetting()
    {
        return $this->hasOne(AutoRestockSetting::class);
    }

    public function aiMetricCaches()
    {
        return $this->hasMany(AiMetricCache::class);
    }

    public function counter()
    {
        return $this->hasOne(StoreCounter::class);
    }

    public function getLogoAttribute()
    {
        if ($this->logo_url) {
            return $this->logo_url;
        }
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('placeholder-product.png');
    }

    public function getCoverAttribute()
    {
        if ($this->cover_url) {
            return $this->cover_url;
        }
        if ($this->cover_path) {
            return asset('storage/' . $this->cover_path);
        }
        return asset('placeholder-product.png');
    }
}
