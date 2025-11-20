<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VatCode extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(VatTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
