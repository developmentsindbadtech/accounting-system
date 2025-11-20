<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(string $module, string $action, string $description, array $metadata = []): void
    {
        $user = Auth::user();

        AuditLog::create([
            'tenant_id' => $user->tenant_id ?? 1,
            'user_id' => $user->id ?? null,
            'actor_name' => $user->name ?? null,
            'actor_email' => $user->email ?? null,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}

