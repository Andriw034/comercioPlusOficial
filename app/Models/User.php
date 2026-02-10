<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

use App\Models\Store;
use App\Models\PublicStore;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /*
    |--------------------------------------------------------------------------
    | Mass assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'phone',
        'avatar_path',
        'avatar',
        'avatar_url',
        'avatar_public_id',
        'status',
        'address',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | API query rules (seguras)
    |--------------------------------------------------------------------------
    */
    protected $allowIncluded = [
        'store',
        'publicStore',
        'carts',
        'orders',
    ];

    protected $allowSort = [
        'name',
        'email',
        'status',
        'created_at',
    ];

    protected $allowFilter = [
        'name',
        'email',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function publicStore()
    {
        return $this->hasOne(PublicStore::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            Store::class,
            'user_id',   // FK en stores
            'store_id',  // FK en products
            'id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFormattedNameAttribute(): string
    {
        return ucwords(strtolower($this->name));
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    }

    /*
    |--------------------------------------------------------------------------
    | Roles helpers (Spatie)
    |--------------------------------------------------------------------------
    */
    public function esComerciante(): bool
    {
        return $this->role === 'merchant' || $this->hasRole('comerciante') || $this->hasRole('merchant');
    }

    public function esCliente(): bool
    {
        return $this->role === 'client' || $this->hasRole('cliente') || $this->hasRole('client');
    }

    public function isMerchant(): bool
    {
        return $this->esComerciante();
    }

    public function isClient(): bool
    {
        return $this->esCliente();
    }

    /*
    |--------------------------------------------------------------------------
    | Store helpers
    |--------------------------------------------------------------------------
    */
    public function hasStore(): bool
    {
        return $this->store()->exists();
    }

    public function hasPublicStore(): bool
    {
        return $this->publicStore()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (API)
    |--------------------------------------------------------------------------
    */
    public function scopeIncluded(Builder $query): Builder
    {
        $relations = array_filter(
            explode(',', request('included', '')),
            fn ($relation) => in_array($relation, $this->allowIncluded)
        );

        return empty($relations) ? $query : $query->with($relations);
    }

    public function scopeFilter(Builder $query): Builder
    {
        foreach (request('filter', []) as $field => $value) {
            if (in_array($field, $this->allowFilter)) {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }

        return $query;
    }

    public function scopeGetOrPaginate(Builder $query)
    {
        $perPage = (int) request('perPage');

        return $perPage > 0
            ? $query->paginate($perPage)
            : $query->get();
    }
}
