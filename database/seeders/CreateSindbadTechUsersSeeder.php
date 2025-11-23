<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateSindbadTechUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create tenant
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('❌ No tenant found. Please run: php artisan setup:database');
            return;
        }

        $this->command->info("✅ Using tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->command->newLine();

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
        $this->command->info('👥 Creating accountant accounts...');
        foreach ($accountants as $accountant) {
            $user = User::where('email', $accountant['email'])->first();
            
            if ($user) {
                $this->command->warn("⚠️  User already exists: {$accountant['email']}");
                // Update password and ensure role is correct
                $user->password = $passwordHash;
                $user->role = $accountant['role'];
                $user->is_active = true;
                $user->save();
                $this->command->info("   ✓ Updated: {$accountant['email']}");
            } else {
                User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $accountant['name'],
                    'email' => $accountant['email'],
                    'password' => $passwordHash,
                    'role' => $accountant['role'],
                    'is_active' => true,
                ]);
                $this->command->info("   ✓ Created: {$accountant['email']}");
            }
        }

        $this->command->newLine();

        // Create admin account
        $this->command->info('👤 Creating admin account...');
        $adminUser = User::where('email', $admin['email'])->first();
        
        if ($adminUser) {
            $this->command->warn("⚠️  Admin user already exists: {$admin['email']}");
            // Update password and ensure role is correct
            $adminUser->password = $passwordHash;
            $adminUser->role = $admin['role'];
            $adminUser->is_active = true;
            $adminUser->save();
            $this->command->info("   ✓ Updated: {$admin['email']}");
        } else {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $passwordHash,
                'role' => $admin['role'],
                'is_active' => true,
            ]);
            $this->command->info("   ✓ Created: {$admin['email']}");
        }

        $this->command->newLine();
        $this->command->info('✅ All users created successfully!');
        $this->command->newLine();
        $this->command->info('📋 Login credentials:');
        $this->command->line("   Password for all accounts: {$password}");
        $this->command->newLine();
    }
}
