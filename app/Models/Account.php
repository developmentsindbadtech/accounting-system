<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'sub_type',
        'description',
        'parent_id',
        'level',
        'is_active',
        'balance',
        'opening_balance',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
            'balance' => 'decimal:2',
            'opening_balance' => 'decimal:2',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function generalLedgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedgerEntry::class);
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
