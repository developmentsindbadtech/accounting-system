<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'line_total',
        'vat_percent',
        'vat_amount',
        'account_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'line_total' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'vat_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
