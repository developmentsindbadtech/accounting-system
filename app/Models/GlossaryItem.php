<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlossaryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'term',
        'type',
        'module',
        'code',
        'description',
        'is_ifrs',
    ];

    protected $casts = [
        'is_ifrs' => 'boolean',
    ];
}
