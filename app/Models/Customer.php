<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'billing_address',
        'shipping_address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'commercial_registration_number',
        'credit_limit',
        'currency',
        'language_preference',
        'balance',
        'is_active',
        'notes',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
