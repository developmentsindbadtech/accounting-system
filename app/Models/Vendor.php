<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
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
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'commercial_registration_number',
        'payment_terms',
        'currency',
        'balance',
        'is_active',
        'notes',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
