<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

// Importa explícitamente los modelos que usas en relaciones y helpers
use App\Models\Store;
use App\Models\PublicStore;
use App\Models\Cart;
use App\Models\Order;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Si usas Spatie con guard web (por defecto), puedes forzarlo aquí si te hiciera falta:
     * protected $guard_name = 'web';
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'address',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $allowIncluded = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'address',
        'role_id'
    ];

    public function store()
    {
        return $this->hasOne(Store::class);
    }
    protected $allowSort = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'address',
        'role_id'
    ];
    protected $allowFilter = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'address',
        'role_id'
    ];

    /* =========================
     * Relaciones
     * ========================= */


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
        return $this->hasManyThrough(Product::class, Store::class, 'user_id', 'id', 'store_id', 'id');
    }

    /* =========================
     * Accessors / Helpers visuales
     * ========================= */

    public function getFormattedNameAttribute()
    {
        return ucwords(strtolower($this->name));
    }

    public function getInitialsAttribute()
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $initials = '';
        foreach ($parts as $part) {
            if ($part !== '') {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
        return $initials;
    }

    /* =========================
     * Reglas de rol (Spatie)
     * ========================= */

    public function esComerciante()
    {
        return $this->hasRole('comerciante');
    }

    public function esCliente()
    {
        return $this->hasRole('cliente');
    }

    /* =========================
     * Helpers para tienda (usados por middleware/flows)
     * ========================= */

    public function hasStore(): bool
    {
        // Si existe relación ->store, usa exists() (evita cargar el modelo completo)
        try {
            if (method_exists($this, 'store')) {
                return (bool) $this->store()->exists();
            }
        } catch (\Throwable $e) {
            // Si por alguna razón falla la relación, hacemos fallback a consulta directa
        }
        return Store::where('user_id', $this->id)->exists();
    }

    public function hasPublicStore(): bool
    {
        try {
            if (method_exists($this, 'publicStore')) {
                return (bool) $this->publicStore()->exists();
            }
        } catch (\Throwable $e) {
            //
        }
        return PublicStore::where('user_id', $this->id)->exists();
    }

    public function scopeIncluded(Builder $query) // Scope local que permite incluir relaciones dinámicamente
    {
        if (empty($this->allowIncluded) || empty(request('included'))) { // Si no hay relaciones permitidas o no se solicitó ninguna
            return $query; // Retorna la consulta sin modificar
        }

        $relations = explode(',', request('included')); // Convierte el string ?included=... en un array (por comas)
        $allowIncluded = collect($this->allowIncluded); // Convierte la lista de relaciones permitidas en una colección

        foreach ($relations as $key => $relationship) { // Recorre cada relación pedida por el usuario
            if (!$allowIncluded->contains($relationship)) { // Si esa relación no está permitida
                unset($relations[$key]); // Se elimina del array para no ser incluida
            }
        }

        return $query->with($relations); // Incluye solo las relaciones válidas en la consulta
    }

    public function scopeFilter(Builder $query) // Scope local que permite aplicar filtros desde la URL (?filter[...]=...)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) { // Si no hay filtros permitidos o no se envió ninguno
            return $query; // Retorna la consulta sin modificar
        }

        $filters = request('filter'); // Obtiene todos los filtros enviados desde la URL
        $allowFilter = collect($this->allowFilter); // Convierte los campos permitidos en colección Laravel

        foreach ($filters as $filter => $value) { // Recorre cada filtro recibido (ej: name => 'HP')
            if ($allowFilter->contains($filter)) { // Si el filtro es uno de los permitidos
                $query->where($filter, 'LIKE', '%' . $value . '%'); // Aplica búsqueda parcial (LIKE '%valor%')
            }
        }

        return $query; // Retorna la consulta modificada con los filtros aplicados
    }

    public function scopeGetOrPaginate(Builder $query)
    {
        if (request('perPage')) {
            $perPage = intval(request('perPage'));

            if ($perPage) {
                return $query->paginate($perPage);
            }
        }

        return $query->get(); // Devuelve todos si no hay perPage
    }
}
