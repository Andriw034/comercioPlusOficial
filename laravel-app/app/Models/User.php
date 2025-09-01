<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'status',
        'address',
        'birthdate',
        'profile',
    ];

    /**
     * Los atributos que deben estar ocultos para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
        'birthdate' => 'date',
        'profile' => 'array',
    ];

    /**
     * Obtener el rol del usuario.
     */
    public function getRoleAttribute($value)
    {
        return $value ?? 'Cliente';
    }

    /**
     * Verificar si el usuario es administrador.
     */
    public function isAdmin()
    {
        return $this->role === 'Administrador';
    }

    /**
     * Verificar si el usuario es comerciante.
     */
    public function isMerchant()
    {
        return $this->role === 'Comerciante';
    }

    /**
     * Verificar si el usuario es cliente.
     */
    public function isClient()
    {
        return $this->role === 'Cliente';
    }
}
