<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar',
        'status', 'address', 'role_id'
    ];

    protected $allowIncluded = [
        'products', 'sales', 'ratings', 'category', 
        'locations', 'notifications', 'setting', 'roles', 'profile'
    ];

    protected $allowSort = [
        'name', 'email', 'password', 'phone', 'avatar',
        'status', 'address', 'role_id'
    ];

    protected $allowFilter = [
        'name', 'email', 'password', 'phone', 'avatar',
        'status', 'address', 'role_id'
    ];

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function locations() {
        return $this->hasMany(Location::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }

    public function notifications() {
        return $this->hasMany(Notification::class);
    }

    public function setting() {
        return $this->hasOne(Setting::class);
    }

    public function publicStores() {
        return $this->hasMany(PublicStore::class);
    }

    public function store() {
        return $this->hasOne(Store::class);
    }

    public function scopeIncluded(Builder $query) {
        if (empty($this->allowIncluded) || empty(request('included'))) {
            return $query;
        }

        $relations = explode(',', request('included'));
        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {
            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }

        return $query->with($relations);
    }

    public function scopeFilter(Builder $query) {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return $query;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            if ($allowFilter->contains($filter)) {
                $query->where($filter, 'LIKE', "%{$value}%");
            }
        }

        return $query;
    }

    public function scopeGetOrPaginate(Builder $query) {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));
            if ($perPage) {
                return $query->paginate($perPage);
            }
        }

        return $query->get();
    }

    public function scopeSort(Builder $query) {
        if (empty($this->allowSort) || empty(request('sort'))) {
            return $query;
        }

        $sortFields = explode(',', request('sort'));
        $allowSort = collect($this->allowSort);

        foreach ($sortFields as $field) {
            $direction = 'asc';
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            }
            if ($allowSort->contains($field)) {
                $query->orderBy($field, $direction);
            }
        }

        return $query->get();
    }
}