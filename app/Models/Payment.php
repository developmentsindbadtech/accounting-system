<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasTenantScope;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'payment_number',
        'customer_id',
        'payment_date',
        'payment_method',
        'reference',
        'amount',
        'invoice_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
