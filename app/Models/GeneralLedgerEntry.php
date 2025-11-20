<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralLedgerEntry extends Model
{
    use HasTenantScope;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'debit',
        'credit',
        'account_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
