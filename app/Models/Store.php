<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'subdomain',
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
        'dian_enabled',
        'dian_nit',
        'dian_business_name',
        'dian_provider',
        'dian_api_credentials',
        'dian_enabled_at',
    ];

    protected $casts = [
        'is_visible'      => 'boolean',
        'is_verified'     => 'boolean',
        'taxes_enabled'       => 'boolean',
        'payment_methods'     => 'array',
        'dian_enabled'        => 'boolean',
        'dian_api_credentials' => 'encrypted:array',
        'dian_enabled_at'     => 'datetime',
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

    public function hasDianEnabled(): bool
    {
        return $this->dian_enabled === true;
    }

    public function enableDian(string $nit, string $businessName, string $provider, array $credentials = []): void
    {
        $this->update([
            'dian_enabled'        => true,
            'dian_nit'            => $nit,
            'dian_business_name'  => $businessName,
            'dian_provider'       => $provider,
            'dian_api_credentials' => $credentials,
            'dian_enabled_at'     => now(),
        ]);
    }

    public function disableDian(): void
    {
        $this->update(['dian_enabled' => false]);
    }

    /**
     * Generar subdomain único basado en nombre.
     */
    public static function generateSubdomain(string $name): string
    {
        $base = Str::slug($name);
        $subdomain = $base;
        $counter = 1;

        while (self::where('subdomain', $subdomain)->exists()) {
            $subdomain = $base . '-' . $counter;
            $counter++;
        }

        return $subdomain;
    }

    /**
     * Obtener URL completa de la tienda (subdominio).
     */
    public function getFullUrlAttribute(): string
    {
        if ($this->subdomain) {
            $domain = config('app.domain', 'localhost');
            $port = config('app.env') === 'local' ? ':8000' : '';
            $protocol = config('app.env') === 'production' ? 'https' : 'http';
            return $protocol . '://' . $this->subdomain . '.' . $domain . $port;
        }

        return config('app.url') . '/store/' . $this->slug;
    }

    /**
     * URL del QR code.
     */
    public function getQrUrlAttribute(): string
    {
        return route('api.qr.generate', ['store' => $this->id]);
    }

    public function getStoreUrl(): string
    {
        $frontendBase = config('app.frontend_url', 'https://comercio-plus-oficial.vercel.app');

        return $frontendBase . '/store/' . ($this->slug ?? $this->id);
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
