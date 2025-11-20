<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenantScope()
    {
        // Auto-set tenant_id to 1 for single-company setup
        static::creating(function ($model) {
            if (!isset($model->tenant_id)) {
                $model->tenant_id = 1;
            }
        });

        // Scope all queries to tenant_id = 1
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', 1);
        });
    }
}

