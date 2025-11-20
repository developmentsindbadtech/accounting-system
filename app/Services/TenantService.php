<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantService
{
    public function createTenant(array $data): Tenant
    {
        // Create tenant record in central database
        $tenant = Tenant::create($data);

        // Create the tenant's database (assuming PostgreSQL)
        $this->createTenantDatabase($tenant);

        return $tenant;
    }

    protected function createTenantDatabase(Tenant $tenant): void
    {
        // Connect to PostgreSQL to create database
        $connectionName = 'pgsql_temp';
        Config::set("database.connections.{$connectionName}", [
            'driver' => 'pgsql',
            'host' => $tenant->database_host,
            'port' => env('DB_PORT', '5432'),
            'database' => 'postgres', // Connect to default database
            'username' => $tenant->database_username,
            'password' => $tenant->database_password,
            'charset' => 'utf8',
        ]);

        DB::connection($connectionName)->statement("CREATE DATABASE {$tenant->database_name}");

        // Run migrations on tenant database
        $tenantConnection = config('tenant.connection_prefix') . $tenant->id;
        Config::set("database.connections.{$tenantConnection}", $tenant->getConnectionConfig());
        
        // Migrate tenant database
        // This would typically be done via a command or queue job
    }
}

