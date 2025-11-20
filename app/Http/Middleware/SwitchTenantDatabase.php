<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip database switching for SQLite (single database mode)
        if (config('database.default') === 'sqlite') {
            return $next($request);
        }

        $tenant = $request->get('tenant');

        if (!$tenant) {
            abort(404, 'Tenant not identified');
        }

        $connectionName = config('tenant.connection_prefix', 'tenant_') . $tenant->id;
        $config = $tenant->getConnectionConfig();

        // Set the tenant connection dynamically
        Config::set("database.connections.{$connectionName}", $config);

        // Set as default connection for this request
        Config::set('database.default', $connectionName);
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        return $next($request);
    }
}
