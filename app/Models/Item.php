<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasTenantScope;
    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'type',
        'category_id',
        'purchase_account_id',
        'sales_account_id',
        'inventory_account_id',
        'unit_of_measure',
        'track_quantity',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_point',
        'standard_cost',
        'is_active',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'track_quantity' => 'boolean',
            'quantity_on_hand' => 'decimal:2',
            'quantity_reserved' => 'decimal:2',
            'reorder_point' => 'decimal:2',
            'standard_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_account_id');
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function getAvailableQuantityAttribute(): float
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }
}
