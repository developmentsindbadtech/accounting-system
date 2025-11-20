<?php

/**
 * Script to create sample users with different roles
 * 
 * Usage: php artisan tinker
 * Then copy and paste the code below, or run: php create-sample-users.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating sample users...\n\n";

// Check if tenant exists
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "❌ Error: No tenant found. Please run 'php artisan setup:database' first.\n";
    exit(1);
}

echo "✅ Using tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Create Accountant User
$accountantEmail = 'accountant@demo.com';
if (User::where('email', $accountantEmail)->exists()) {
    echo "⚠️  Accountant user already exists: {$accountantEmail}\n";
} else {
    $accountant = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Accountant User',
        'email' => $accountantEmail,
        'password' => Hash::make('password'),
        'role' => 'accountant',
        'is_active' => true,
    ]);
    echo "✅ Created Accountant user:\n";
    echo "   Email: {$accountantEmail}\n";
    echo "   Password: password\n";
    echo "   Role: accountant\n\n";
}

// Create Viewer User
$viewerEmail = 'viewer@demo.com';
if (User::where('email', $viewerEmail)->exists()) {
    echo "⚠️  Viewer user already exists: {$viewerEmail}\n";
} else {
    $viewer = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Viewer User',
        'email' => $viewerEmail,
        'password' => Hash::make('password'),
        'role' => 'viewer',
        'is_active' => true,
    ]);
    echo "✅ Created Viewer user:\n";
    echo "   Email: {$viewerEmail}\n";
    echo "   Password: password\n";
    echo "   Role: viewer\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 Summary of All Users:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$users = User::all();
foreach ($users as $user) {
    echo "👤 {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Role: {$user->role}\n";
    echo "   Status: " . ($user->is_active ? 'Active' : 'Inactive') . "\n\n";
}

echo "✅ Done! You can now login with these accounts.\n";

