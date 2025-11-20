<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationEntry extends Model
{
    use HasTenantScope;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'fixed_asset_id',
        'period',
        'depreciation_amount',
        'accumulated_depreciation',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
