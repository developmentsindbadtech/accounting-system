<?php

namespace App\Models;

use App\Models\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatTransaction extends Model
{
    use HasTenantScope;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'vat_code_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'vat_amount',
        'net_amount',
        'gross_amount',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'vat_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'transaction_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function vatCode(): BelongsTo
    {
        return $this->belongsTo(VatCode::class);
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
