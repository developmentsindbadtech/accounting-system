<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'tax_invoice_number',
        'qr_code',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'vat_amount',
        'total',
        'currency',
        'exchange_rate',
        'status',
        'invoice_type',
        'sales_representative',
        'payment_terms',
        'reference',
        'notes',
        'amount_paid',
        'balance_due',
        'created_by',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['sent', 'overdue']);
    }
}
