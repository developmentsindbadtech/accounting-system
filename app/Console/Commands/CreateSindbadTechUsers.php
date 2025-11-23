<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSindbadTechUsers extends Command
{
    protected $signature = 'users:create-sindbad-tech';

    protected $description = 'Create accountant and admin accounts for Sindbad Tech team';

    public function handle()
    {
        $this->info('👥 Creating Sindbad Tech user accounts...');
        $this->newLine();

        // Get or create tenant
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->error('❌ No tenant found. Please run: php artisan setup:database');
            return 1;
        }

        $this->info("✅ Using tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->newLine();

        $password = 'Ksa@2021!';
        $passwordHash = Hash::make($password);

        // Accountant accounts
        $accountants = [
            [
                'name' => 'Reve Mar Surigao',
                'email' => 'revemar.surigao@sindbad.tech',
                'role' => 'accountant',
            ],
            [
                'name' => 'Hazel Bacalso',
                'email' => 'hazel.bacalso@sindbad.tech',
                'role' => 'accountant',
            ],
            [
                'name' => 'Aziz Alsultan',
                'email' => 'aziz.alsultan@sindbad.tech',
                'role' => 'accountant',
            ],
            [
                'name' => 'Mohammed Agbawi',
                'email' => 'mohammed.agbawi@sindbad.tech',
                'role' => 'accountant',
            ],
        ];

        // Admin account
        $admin = [
            'name' => 'Development Admin',
            'email' => 'development@sindbad.tech',
            'role' => 'admin',
        ];

        // Create accountant accounts
        $this->info('👥 Creating accountant accounts...');
        foreach ($accountants as $accountant) {
            $user = User::where('email', $accountant['email'])->first();
            
            if ($user) {
                $this->warn("⚠️  User already exists: {$accountant['email']}");
                // Update password and ensure role is correct
                $user->password = $passwordHash;
                $user->role = $accountant['role'];
                $user->is_active = true;
                $user->save();
                $this->info("   ✓ Updated: {$accountant['email']}");
            } else {
                User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $accountant['name'],
                    'email' => $accountant['email'],
                    'password' => $passwordHash,
                    'role' => $accountant['role'],
                    'is_active' => true,
                ]);
                $this->info("   ✓ Created: {$accountant['email']}");
            }
        }

        $this->newLine();

        // Create admin account
        $this->info('👤 Creating admin account...');
        $adminUser = User::where('email', $admin['email'])->first();
        
        if ($adminUser) {
            $this->warn("⚠️  Admin user already exists: {$admin['email']}");
            // Update password and ensure role is correct
            $adminUser->password = $passwordHash;
            $adminUser->role = $admin['role'];
            $adminUser->is_active = true;
            $adminUser->save();
            $this->info("   ✓ Updated: {$admin['email']}");
        } else {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $passwordHash,
                'role' => $admin['role'],
                'is_active' => true,
            ]);
            $this->info("   ✓ Created: {$admin['email']}");
        }

        $this->newLine();
        $this->info('✅ All users created successfully!');
        $this->newLine();
        $this->info('📋 Login credentials:');
        $this->line("   Password for all accounts: {$password}");
        $this->newLine();
        
        return 0;
    }
}
