<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'bill_number',
        'tax_invoice_number',
        'qr_code',
        'vendor_id',
        'bill_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'vat_amount',
        'total',
        'currency',
        'exchange_rate',
        'status',
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
            'bill_date' => 'date',
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

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BillLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
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
        return $query->whereIn('status', ['received', 'overdue']);
    }
}
