<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Identification Method
    |--------------------------------------------------------------------------
    |
    | This value determines how tenants are identified in the application.
    | Supported: 'domain', 'subdomain', 'header'
    |
    */
    'identification' => env('TENANT_IDENTIFICATION', 'header'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Header Name
    |--------------------------------------------------------------------------
    |
    | When using header identification, this is the header name to check.
    |
    */
    'header_name' => env('TENANT_HEADER_NAME', 'X-Tenant-ID'),

    /*
    |--------------------------------------------------------------------------
    | Central Database Connection
    |--------------------------------------------------------------------------
    |
    | The connection name for the central database where tenant records
    | are stored.
    |
    */
    'central_connection' => env('DB_CENTRAL_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix is used when creating dynamic tenant database connections.
    |
    */
    'connection_prefix' => 'tenant_',
];

