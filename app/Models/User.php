<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
        // Si decides guardar más datos (phone, avatar, etc.) los agregas acá
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // En Laravel 11 puedes usar el cast hashed para encriptar la contraseña automáticamente
        'password' => 'hashed',
    ];

    /* =========================
     * Relaciones
     * ========================= */

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
}
