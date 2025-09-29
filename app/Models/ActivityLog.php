<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'user_id',
        'user_name',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'description',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó la actividad
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo afectado por la actividad
     */
    public function getModel()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para actividades recientes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Obtener descripción formateada de la actividad
     */
    public function getFormattedDescriptionAttribute()
    {
        if ($this->description) {
            return $this->description;
        }

        // Generar descripción automática basada en la acción y modelo
        $modelName = $this->getModelName();
        $userName = $this->user_name ?: 'Usuario desconocido';

        return match ($this->action) {
            'login' => "{$userName} inició sesión",
            'logout' => "{$userName} cerró sesión",
            'create' => "{$userName} creó un nuevo {$modelName}",
            'update' => "{$userName} actualizó un {$modelName}",
            'delete' => "{$userName} eliminó un {$modelName}",
            'view' => "{$userName} visualizó un {$modelName}",
            default => "{$userName} realizó la acción '{$this->action}' en {$modelName}",
        };
    }

    /**
     * Obtener nombre legible del modelo
     */
    private function getModelName()
    {
        if (!$this->model_type) {
            return 'elemento';
        }

        return match ($this->model_type) {
            'App\Models\User' => 'usuario',
            'App\Models\Product' => 'producto',
            'App\Models\Store' => 'tienda',
            'App\Models\Order' => 'pedido',
            'App\Models\Category' => 'categoría',
            'App\Models\Cart' => 'carrito',
            default => strtolower(class_basename($this->model_type)),
        };
    }

    /**
     * Verificar si la actividad es crítica
     */
    public function isCritical()
    {
        return in_array($this->action, ['delete', 'login', 'logout']);
    }

    /**
     * Obtener icono para la actividad
     */
    public function getIconAttribute()
    {
        return match ($this->action) {
            'login' => 'login',
            'logout' => 'logout',
            'create' => 'plus',
            'update' => 'edit',
            'delete' => 'trash',
            'view' => 'eye',
            default => 'activity',
        };
    }

    /**
     * Obtener color para la actividad
     */
    public function getColorAttribute()
    {
        return match ($this->action) {
            'login', 'create' => 'green',
            'logout', 'delete' => 'red',
            'update' => 'blue',
            'view' => 'gray',
            default => 'yellow',
        };
    }
}
