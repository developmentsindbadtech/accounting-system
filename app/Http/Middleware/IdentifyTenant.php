<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Single-company setup: Always use tenant ID 1
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'My Company',
                'database_name' => config('database.connections.' . config('database.default') . '.database', 'accounting'),
                'database_host' => config('database.connections.' . config('database.default') . '.host', '127.0.0.1'),
                'database_username' => config('database.connections.' . config('database.default') . '.username', ''),
                'database_password' => config('database.connections.' . config('database.default') . '.password', ''),
                'is_active' => true,
            ]
        );

        $request->merge(['tenant' => $tenant]);
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
