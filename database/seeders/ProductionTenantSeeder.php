<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductionTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder ensures at least one tenant exists for Azure SSO user creation.
     */
    public function run(): void
    {
        // Only create if no tenants exist
        if (Tenant::count() === 0) {
            $tenant = Tenant::create([
                'name' => env('TENANT_NAME', 'Sindbad Tech'),
                'domain' => env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : 'stas.sindbad.tech',
                'database_name' => env('DB_DATABASE'),
                'database_host' => env('DB_HOST', '127.0.0.1'),
                'database_username' => env('DB_USERNAME'),
                'database_password' => env('DB_PASSWORD'),
                'is_active' => true,
            ]);

            $this->command->info("✅ Tenant created: {$tenant->name} (ID: {$tenant->id})");
        } else {
            $count = Tenant::count();
            $this->command->info("ℹ️  Tenant(s) already exist. Count: {$count}");
            
            // Display existing tenants
            Tenant::all()->each(function ($tenant) {
                $this->command->line("  - {$tenant->name} (ID: {$tenant->id}, Domain: {$tenant->domain})");
            });
        }
    }
}

