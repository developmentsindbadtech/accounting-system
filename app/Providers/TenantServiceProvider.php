<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ensure we're using the central database connection for tenants table
        // This will be handled by the middleware, but we can set up defaults here
    }
}
