<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSampleUsers extends Command
{
    protected $signature = 'users:create-sample';

    protected $description = 'Create sample users with accountant and viewer roles';

    public function handle()
    {
        $this->info('👥 Creating sample users...');
        $this->newLine();

        // Get tenant
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->error('❌ No tenant found. Please run: php artisan setup:database');
            return 1;
        }

        $this->info("✅ Using tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->newLine();

        // Create Accountant User
        $accountantEmail = 'accountant@demo.com';
        if (User::where('email', $accountantEmail)->exists()) {
            $this->warn("⚠️  Accountant user already exists: {$accountantEmail}");
        } else {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Accountant User',
                'email' => $accountantEmail,
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'is_active' => true,
            ]);
            $this->info("✅ Created Accountant user:");
            $this->line("   Email: {$accountantEmail}");
            $this->line("   Password: password");
            $this->line("   Role: accountant");
        }
        $this->newLine();

        // Create Viewer User
        $viewerEmail = 'viewer@demo.com';
        if (User::where('email', $viewerEmail)->exists()) {
            $this->warn("⚠️  Viewer user already exists: {$viewerEmail}");
        } else {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Viewer User',
                'email' => $viewerEmail,
                'password' => Hash::make('password'),
                'role' => 'viewer',
                'is_active' => true,
            ]);
            $this->info("✅ Created Viewer user:");
            $this->line("   Email: {$viewerEmail}");
            $this->line("   Password: password");
            $this->line("   Role: viewer");
        }
        $this->newLine();

        // Display all users
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📋 All Users in System:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $users = User::all(['name', 'email', 'role', 'is_active']);
        $tableData = $users->map(function ($user) {
            return [
                'Name' => $user->name,
                'Email' => $user->email,
                'Role' => $user->role,
                'Status' => $user->is_active ? 'Active' : 'Inactive',
            ];
        })->toArray();

        $this->table(['Name', 'Email', 'Role', 'Status'], $tableData);
        $this->newLine();

        $this->info('✅ Done! You can now login with these accounts.');
        $this->newLine();
        $this->info('🔑 Login Credentials:');
        $this->line('   Admin:     admin@demo.com / password');
        $this->line('   Accountant: accountant@demo.com / password');
        $this->line('   Viewer:    viewer@demo.com / password');

        return 0;
    }
}

