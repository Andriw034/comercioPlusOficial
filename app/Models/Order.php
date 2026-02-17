<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderProduct;
use App\Models\Store;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'total',
        'date',
        'items',
        'customer',
        'customer_email',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_city',
        'total_amount',
        'payment_method',
        'status',
        'payment_reference',
        'wompi_transaction_id',
        'payment_status',
        'payment_approved_at',
        'payment_failed_at',
        'wompi_data',
    ];

    protected $casts = [
        'items' => 'array',
        'customer' => 'array',
        'wompi_data' => 'array',
        'total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'date' => 'datetime',
        'payment_approved_at' => 'datetime',
        'payment_failed_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Legacy name used in existing controllers/tests.
    public function ordenproducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Scope para órdenes pagadas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para órdenes pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Marcar orden como pagada
     */
    public function markAsPaid($wompiData = null)
    {
        $this->update([
            'status' => 'paid',
            'payment_status' => 'approved',
            'payment_approved_at' => now(),
            'wompi_data' => $wompiData,
        ]);
    }

    /**
     * Marcar orden como fallida
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'payment_failed',
            'payment_status' => 'failed',
            'payment_failed_at' => now(),
            'wompi_data' => $errorMessage ? ['error' => $errorMessage] : null,
        ]);
    }
}
