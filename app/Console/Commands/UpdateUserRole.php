<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role {email} {role : The role to assign (admin, accountant, viewer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a user\'s role by email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role = strtolower($this->argument('role'));

        if (!in_array($role, ['admin', 'accountant', 'viewer'])) {
            $this->error('Invalid role. Must be: admin, accountant, or viewer');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $oldRole = $user->role;
        $user->role = $role;
        $user->save();

        $this->info("✅ User '{$email}' role updated from '{$oldRole}' to '{$role}'");
        
        return 0;
    }
}
