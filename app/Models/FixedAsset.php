<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'asset_number',
        'name',
        'category_id',
        'purchase_date',
        'purchase_cost',
        'useful_life_years',
        'depreciation_method',
        'salvage_value',
        'accumulated_depreciation',
        'net_book_value',
        'asset_account_id',
        'depreciation_expense_account_id',
        'accumulated_depreciation_account_id',
        'status',
        'disposal_date',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'useful_life_years' => 'integer',
            'salvage_value' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'net_book_value' => 'decimal:2',
            'disposal_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationEntries(): HasMany
    {
        return $this->hasMany(DepreciationEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
