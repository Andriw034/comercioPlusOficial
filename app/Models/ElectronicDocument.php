<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectronicDocument extends Model
{
    use SoftDeletes;

    const TYPE_INVOICE = 'invoice';
    const TYPE_CREDIT_NOTE = 'credit_note';
    const TYPE_DEBIT_NOTE = 'debit_note';

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'store_id',
        'order_id',
        'document_type',
        'prefix',
        'number',
        'cufe',
        'cude',
        'dian_status',
        'dian_track_id',
        'dian_approved_at',
        'dian_response_message',
        'issuer_nit',
        'issuer_name',
        'issuer_email',
        'issuer_phone',
        'issuer_address',
        'issuer_city',
        'issuer_department',
        'customer_identification_type',
        'customer_identification',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_department',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'currency',
        'payment_method',
        'payment_means',
        'payment_due_date',
        'xml_content',
        'xml_signed',
        'pdf_path',
        'qr_code',
        'reference_document_id',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dian_approved_at' => 'datetime',
        'payment_due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'number' => 'integer',
    ];

    // ─── Relaciones ───

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ElectronicDocumentItem::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(ElectronicDocumentTax::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ElectronicDocumentLog::class);
    }

    /** Documento origen (para notas crédito/débito) */
    public function referenceDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reference_document_id');
    }

    /** Notas que referencian este documento */
    public function referencedBy(): HasMany
    {
        return $this->hasMany(self::class, 'reference_document_id');
    }

    // ─── Scopes ───

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('dian_status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('dian_status', self::STATUS_PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('dian_status', self::STATUS_REJECTED);
    }

    public function scopeInvoices(Builder $query): Builder
    {
        return $query->where('document_type', self::TYPE_INVOICE);
    }

    public function scopeCreditNotes(Builder $query): Builder
    {
        return $query->where('document_type', self::TYPE_CREDIT_NOTE);
    }

    public function scopeDebitNotes(Builder $query): Builder
    {
        return $query->where('document_type', self::TYPE_DEBIT_NOTE);
    }

    // ─── Accessors ───

    public function getFullNumberAttribute(): string
    {
        return $this->prefix . '-' . str_pad($this->number, 10, '0', STR_PAD_LEFT);
    }

    // ─── Métodos helper ───

    public function isApproved(): bool
    {
        return $this->dian_status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->dian_status === self::STATUS_REJECTED;
    }

    public function isDraft(): bool
    {
        return $this->dian_status === self::STATUS_DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return $this->isApproved()
            && $this->created_at->diffInDays(now()) < 5;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->dian_status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }
}
