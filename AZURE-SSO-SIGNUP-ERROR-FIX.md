# Azure SSO Sign-Up Error - 500 Internal Server Error

## Issue Summary
Users attempting to sign up via Azure SSO on production (`https://stas.sindbad.tech`) are encountering a **500 Internal Server Error** after successful authentication with Microsoft.

### What's Happening
1. ✅ Microsoft Azure AD authentication works correctly (returns authorization code)
2. ✅ Callback URL is reached: `https://stas.sindbad.tech/login/azure/callback`
3. ❌ Application returns 500 error when processing the callback

## Root Cause
The `AzureController` callback method tries to create new users with a `tenant_id`, but:
- The code was trying to get the first tenant ID or default to `1`
- If no tenant exists in the database, or no tenant with ID `1` exists, the user creation fails with a **foreign key constraint violation**
- This causes an unhandled database exception → 500 error

## Solution

### ✅ Code Fix (Already Applied)
The code has been updated with:
1. **Better tenant validation** - checks if tenant exists before creating user
2. **Improved error handling** - catches database exceptions gracefully
3. **Enhanced logging** - logs all Azure SSO operations for debugging
4. **User-friendly error messages** - displays appropriate messages instead of 500 errors

### 🔧 DevOps Action Required (URGENT)

#### Step 1: Verify Tenant Exists in Production Database

SSH into the production server and run:

```bash
cd /path/to/accounting-system
php artisan tinker
```

Then in Tinker:
```php
// Check if any tenants exist
Tenant::count();

// View the first tenant
Tenant::first();
```

#### Step 2: Create Tenant If None Exists

If no tenant exists, create one:

```php
Tenant::create([
    'name' => 'Sindbad Tech',
    'domain' => 'stas.sindbad.tech',
    'database_name' => env('DB_DATABASE'),
    'database_host' => env('DB_HOST'),
    'database_username' => env('DB_USERNAME'),
    'database_password' => env('DB_PASSWORD'),
    'is_active' => true
]);
```

**Or using a migration/seeder:**

Create a file `database/seeders/ProductionTenantSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductionTenantSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::count() === 0) {
            Tenant::create([
                'name' => 'Sindbad Tech',
                'domain' => 'stas.sindbad.tech',
                'database_name' => env('DB_DATABASE'),
                'database_host' => env('DB_HOST', '127.0.0.1'),
                'database_username' => env('DB_USERNAME'),
                'database_password' => env('DB_PASSWORD'),
                'is_active' => true,
            ]);
        }
    }
}
```

Then run:
```bash
php artisan db:seed --class=ProductionTenantSeeder
```

#### Step 3: Deploy Updated Code

1. **Pull the latest code** with the fixes:
```bash
git pull origin main
```

2. **Clear caches**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

3. **Restart services** (if using PHP-FPM/supervisor):
```bash
sudo systemctl restart php8.2-fpm
# or
sudo supervisorctl restart all
```

#### Step 4: Monitor Logs

Watch the logs for incoming Azure SSO attempts:
```bash
tail -f storage/logs/laravel.log
```

Look for these new log entries:
- `Creating new user via Azure SSO`
- `User successfully logged in via Azure SSO`
- `No tenant found in database during Azure SSO signup` (if tenant still missing)
- `Database error during Azure SSO callback` (if other DB issues)

#### Step 5: Verify Environment Variables

Ensure these are set in production `.env`:
```bash
AZURE_AD_CLIENT_ID=your_azure_client_id
AZURE_AD_CLIENT_SECRET=your_azure_client_secret
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback
AZURE_AD_TENANT_ID=your_tenant_id
```

## Testing After Fix

1. **Clear browser cookies/cache** for `stas.sindbad.tech`
2. **Attempt Azure SSO login** with a test user
3. **Check logs** to verify:
   - User creation or login is logged
   - No errors appear
   - Redirect to dashboard succeeds

## Expected Log Output (Success)

```
[2025-11-21 10:30:00] production.INFO: Creating new user via Azure SSO {"email":"user@sindbad.tech","role":"viewer","tenant_id":1}
[2025-11-21 10:30:00] production.INFO: User successfully logged in via Azure SSO {"email":"user@sindbad.tech","role":"viewer"}
```

## Expected Log Output (If Tenant Missing - Before Fix)

```
[2025-11-21 10:30:00] production.ERROR: No tenant found in database during Azure SSO signup {"email":"user@sindbad.tech","azure_id":"..."}
```

## Additional Notes

### Multi-Tenant Consideration
If this system is meant to be multi-tenant and each organization should have its own tenant:
1. Create a tenant management interface
2. Implement tenant identification logic in the middleware
3. Update Azure SSO to map users to appropriate tenants (e.g., by email domain)

### Single-Tenant System
If this is a single-tenant system:
1. Ensure exactly ONE tenant exists in production
2. Consider adding a database constraint or validation
3. Create the tenant as part of the deployment process

## Support

If issues persist after following these steps:
1. Check production logs: `storage/logs/laravel.log`
2. Verify database connectivity
3. Confirm Azure AD app registration settings
4. Test with `php artisan tinker` to create test users manually

## Files Changed
- `app/Http/Controllers/Auth/AzureController.php` - Enhanced error handling and logging

