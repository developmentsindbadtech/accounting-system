<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupDatabase extends Command
{
    protected $signature = 'setup:database {--fresh : Drop existing databases and recreate}';

    protected $description = 'Setup the application databases and create initial tenant and user';

    public function handle()
    {
        $this->info('🚀 Setting up Sindbad.Tech Accounting System...');

        // Create central database if not exists
        $this->info('📦 Creating central database...');
        try {
            $this->createDatabase('accounting_central');
            $this->info('✅ Central database created/verified');
        } catch (\Exception $e) {
            $this->error('❌ Error creating central database: ' . $e->getMessage());
            $this->warn('Please create the database manually: CREATE DATABASE accounting_central;');
            if (!$this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        // Run migrations on central database
        $this->info('🔄 Running migrations on central database...');
        $this->call('migrate');

        // Create tenant database
        $tenantDbName = 'tenant_demo';
        $this->info("📦 Creating tenant database: {$tenantDbName}...");
        try {
            $this->createDatabase($tenantDbName);
            $this->info('✅ Tenant database created/verified');
        } catch (\Exception $e) {
            $this->error('❌ Error creating tenant database: ' . $e->getMessage());
            $this->warn("Please create the database manually: CREATE DATABASE {$tenantDbName};");
            if (!$this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        // Create or get tenant
        $this->info('🏢 Creating tenant...');
        $tenant = Tenant::firstOrCreate(
            ['database_name' => $tenantDbName],
            [
                'name' => 'Demo Company',
                'domain' => null,
                'database_host' => env('DB_HOST', '127.0.0.1'),
                'database_username' => env('DB_USERNAME', 'postgres'),
                'database_password' => env('DB_PASSWORD', ''),
                'is_active' => true,
            ]
        );

        // Switch to tenant database and run migrations
        $this->info('🔄 Running migrations on tenant database...');
        $this->runTenantMigrations($tenant);

        // Create admin user
        $this->info('👤 Creating admin user...');
        $email = 'admin@demo.com';
        $password = 'password';

        if (!User::where('email', $email)->exists()) {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin User',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->info("✅ Admin user created!");
        } else {
            $this->info("✅ Admin user already exists");
        }

        $this->newLine();
        $this->info('✅ Setup complete!');
        $this->newLine();
        $this->info('📋 Login credentials:');
        $this->line("   Email: {$email}");
        $this->line("   Password: {$password}");
        $this->newLine();
        $this->info('🚀 Next steps:');
        $this->line('   1. Run: php artisan serve (Terminal 1)');
        $this->line('   2. Run: npm run dev (Terminal 2)');
        $this->line('   3. Open: http://localhost:8000');

        return 0;
    }

    protected function createDatabase($databaseName)
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '5432');
        $username = env('DB_USERNAME', 'postgres');
        $password = env('DB_PASSWORD', '');
        
        // Connect to postgres database to create new database
        $tempConnection = [
            'driver' => 'pgsql',
            'host' => $host,
            'port' => $port,
            'database' => 'postgres', // Connect to default postgres DB
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ];

        try {
            config(['database.connections.temp_setup' => $tempConnection]);
            
            DB::connection('temp_setup')->statement("CREATE DATABASE {$databaseName}");
            
            // Clean up
            DB::purge('temp_setup');
        } catch (\PDOException $e) {
            // Database might already exist, which is fine
            if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'duplicate')) {
                return; // Database exists, that's okay
            }
            // Re-throw if it's a different error
            throw $e;
        }
    }

    protected function runTenantMigrations(Tenant $tenant)
    {
        // Temporarily switch to tenant connection
        $connectionName = 'tenant_' . $tenant->id;
        $config = $tenant->getConnectionConfig();
        
        config(["database.connections.{$connectionName}" => $config]);
        
        // Set as default temporarily
        $originalDefault = config('database.default');
        $originalDbName = config("database.connections.{$originalDefault}.database");
        
        config(['database.default' => $connectionName]);
        
        try {
            DB::purge($connectionName);
            DB::purge($originalDefault);
            
            // Reconnect to tenant database
            DB::reconnect($connectionName);
            
            // Verify connection works
            DB::connection($connectionName)->getPdo();
            
            // Run migrations
            $this->call('migrate', ['--database' => $connectionName, '--force' => true]);
            
        } catch (\Exception $e) {
            $this->error("Error running tenant migrations: " . $e->getMessage());
            $this->warn("You may need to run migrations manually on tenant database");
        } finally {
            // Restore original default
            config(['database.default' => $originalDefault]);
            DB::purge($connectionName);
        }
    }
}
