<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'name',
        'depreciation_rate',
    ];

    protected function casts(): array
    {
        return [
            'depreciation_rate' => 'decimal:2',
        ];
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }
}
