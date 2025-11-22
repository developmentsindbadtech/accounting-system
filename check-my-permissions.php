#!/usr/bin/env php
<?php

/**
 * Quick User Permission Checker
 * Run: php check-my-permissions.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "\n=== USER PERMISSION CHECKER ===\n\n";

// Get all users
$users = \App\Models\User::all();

if ($users->isEmpty()) {
    echo "❌ No users found in database!\n";
    echo "   Create a user first.\n\n";
    exit(1);
}

echo "Found " . $users->count() . " user(s):\n\n";

foreach ($users as $user) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "👤 User: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Role: " . ($user->role ?? 'NULL') . "\n";
    echo "   Active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
    echo "\n";
    echo "   Permissions:\n";
    echo "   • isAdmin(): " . ($user->isAdmin() ? '✅ YES' : '❌ NO') . "\n";
    echo "   • isAccountant(): " . ($user->isAccountant() ? '✅ YES' : '❌ NO') . "\n";
    echo "   • canEdit(): " . ($user->canEdit() ? '✅ YES' : '❌ NO') . "\n";
    echo "   • isViewer(): " . ($user->isViewer() ? '✅ YES' : '❌ NO') . "\n";
    echo "\n";
    
    if ($user->canEdit()) {
        echo "   🎉 This user CAN download CSV files!\n";
    } else {
        echo "   ⚠️  This user CANNOT download CSV files!\n";
        echo "   ℹ️  To fix: Change role to 'admin' or 'accountant'\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Ask if they want to fix a user
echo "Do you want to change a user's role to 'admin'? (y/n): ";
$handle = fopen ("php://stdin","r");
$line = trim(fgets($handle));

if(strtolower($line) === 'y' || strtolower($line) === 'yes'){
    echo "Enter user email: ";
    $email = trim(fgets($handle));
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ User not found: $email\n\n";
        exit(1);
    }
    
    echo "Current role: " . ($user->role ?? 'NULL') . "\n";
    echo "Change to: ";
    $newRole = trim(fgets($handle));
    
    if (!in_array($newRole, ['admin', 'accountant', 'viewer'])) {
        echo "❌ Invalid role. Must be: admin, accountant, or viewer\n\n";
        exit(1);
    }
    
    $user->role = $newRole;
    $user->is_active = true;
    $user->save();
    
    echo "\n✅ Updated {$user->email} to role: {$newRole}\n";
    echo "   Can edit: " . ($user->canEdit() ? 'YES' : 'NO') . "\n";
    echo "   Can download CSV: " . ($user->canEdit() ? 'YES' : 'NO') . "\n\n";
} else {
    echo "\nNo changes made.\n\n";
}

fclose($handle);

echo "Done!\n\n";

