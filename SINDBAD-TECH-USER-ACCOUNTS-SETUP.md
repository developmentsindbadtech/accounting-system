# Sindbad Tech User Accounts Setup

## Summary

This document describes the setup of user accounts for the Sindbad Tech Accounting System.

## Changes Made

### 1. ✅ Created User Accounts

Created accounts for accountant and admin users:

**Accountant Accounts (all with accountant role):**
- revemar.surigao@sindbad.tech
- hazel.bacalso@sindbad.tech
- aziz.alsultan@sindbad.tech
- mohammed.agbawi@sindbad.tech

**Admin Account:**
- development@sindbad.tech

**Password for all accounts:** `Ksa@2021!`

### 2. ✅ Added Email/Password Login Form

- Added email and password login form below the Microsoft SSO button on the login page
- Users can now login using either:
  - Microsoft SSO (Azure AD)
  - Email/Password credentials

### 3. ✅ Fixed SSO Auto-Creation

- SSO now automatically creates users from @sindbad.tech domain
- Automatically creates default tenant if none exists (prevents "contact admin" errors)
- Auto-assigns roles based on email:
  - Known accountants → `accountant` role
  - Known admins → `admin` role
  - Other @sindbad.tech users → `accountant` role (default)
- Improved error handling to avoid showing "contact admin" messages

## How to Create Users

### Option 1: Using Artisan Command (Recommended)

```bash
php artisan users:create-sindbad-tech
```

This command will:
- Create all accountant accounts
- Create admin account
- Update passwords if accounts already exist
- Use tenant from database

### Option 2: Using Seeder

```bash
php artisan db:seed --class=CreateSindbadTechUsersSeeder
```

### Option 3: Using Tinker

```bash
php artisan tinker
```

Then paste:

```php
$tenant = \App\Models\Tenant::first();
$password = \Illuminate\Support\Facades\Hash::make('Ksa@2021!');

// Accountants
$accountants = [
    ['name' => 'Reve Mar Surigao', 'email' => 'revemar.surigao@sindbad.tech'],
    ['name' => 'Hazel Bacalso', 'email' => 'hazel.bacalso@sindbad.tech'],
    ['name' => 'Aziz Alsultan', 'email' => 'aziz.alsultan@sindbad.tech'],
    ['name' => 'Mohammed Agbawi', 'email' => 'mohammed.agbawi@sindbad.tech'],
];

foreach ($accountants as $acc) {
    \App\Models\User::updateOrCreate(
        ['email' => $acc['email']],
        [
            'tenant_id' => $tenant->id,
            'name' => $acc['name'],
            'email' => $acc['email'],
            'password' => $password,
            'role' => 'accountant',
            'is_active' => true,
        ]
    );
}

// Admin
\App\Models\User::updateOrCreate(
    ['email' => 'development@sindbad.tech'],
    [
        'tenant_id' => $tenant->id,
        'name' => 'Development Admin',
        'email' => 'development@sindbad.tech',
        'password' => $password,
        'role' => 'admin',
        'is_active' => true,
    ]
);

exit
```

## Login Instructions

### Via Email/Password

1. Go to the login page
2. Scroll down below the Microsoft SSO button
3. Enter email and password
4. Click "Sign in"

### Via Microsoft SSO

1. Click "Sign in with Microsoft"
2. Authenticate with Microsoft/Azure AD
3. User will be automatically created if from @sindbad.tech domain

## Files Modified

1. **`resources/views/auth/login.blade.php`**
   - Added email/password login form below SSO button

2. **`app/Http/Controllers/Auth/AzureController.php`**
   - Auto-create tenant if none exists
   - Auto-create users from @sindbad.tech domain
   - Auto-assign roles based on email
   - Improved error handling

3. **`database/seeders/CreateSindbadTechUsersSeeder.php`** (NEW)
   - Seeder to create all Sindbad Tech user accounts

4. **`app/Console/Commands/CreateSindbadTechUsers.php`** (NEW)
   - Artisan command to create users: `php artisan users:create-sindbad-tech`

## Testing

After creating accounts, verify they can login:

1. **Test Email/Password Login:**
   - Go to login page
   - Use any accountant email with password `Ksa@2021!`
   - Should successfully login

2. **Test SSO Login:**
   - Click "Sign in with Microsoft"
   - Use any @sindbad.tech account
   - Should automatically create user and login

3. **Verify Roles:**
   - Login as accountant → should see accountant permissions
   - Login as admin → should see admin permissions

## Notes

- All passwords are: `Ksa@2021!`
- Users can login via either email/password or Microsoft SSO
- SSO users are automatically created for @sindbad.tech domain
- Roles are automatically assigned based on email address
- If tenant doesn't exist, SSO will create a default one
