# Quick Deployment Commands for Azure SSO Fix

## 🚨 URGENT: Fix for Production 500 Error

### Step 1: Deploy Code Changes
```bash
# SSH to production server
cd /var/www/accounting-system  # or your path

# Pull latest code
git pull origin main

# Install any new dependencies (if needed)
composer install --no-dev --optimize-autoloader
```

### Step 2: Create Tenant (CRITICAL)
This is the **root cause** of the 500 error. Choose ONE method:

#### Method A: Using Seeder (Recommended)
```bash
php artisan db:seed --class=ProductionTenantSeeder
```

#### Method B: Using Tinker
```bash
php artisan tinker
```
Then run:
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
exit
```

### Step 3: Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 4: Restart Services
```bash
# For PHP-FPM
sudo systemctl restart php8.2-fpm

# For Nginx
sudo systemctl restart nginx

# For Supervisor (if using queues)
sudo supervisorctl restart all
```

### Step 5: Verify Fix
```bash
# Monitor logs in real-time
tail -f storage/logs/laravel.log
```

Then test Azure SSO login from the browser.

## Expected Success Log Output
```
[2025-11-21 XX:XX:XX] production.INFO: Creating new user via Azure SSO {"email":"user@sindbad.tech","role":"viewer","tenant_id":1}
[2025-11-21 XX:XX:XX] production.INFO: User successfully logged in via Azure SSO {"email":"user@sindbad.tech","role":"viewer"}
```

## Verification Checklist
- [ ] Code deployed to production
- [ ] Tenant exists in database (verified with `php artisan tinker` → `Tenant::count()`)
- [ ] Caches cleared
- [ ] Services restarted
- [ ] Test Azure SSO login successful
- [ ] Check logs show successful login

## Troubleshooting

### If still getting 500 error:
```bash
# Check if tenant exists
php artisan tinker
>>> Tenant::count()
>>> Tenant::first()
```

### If logs show "No tenant found":
Run the seeder again:
```bash
php artisan db:seed --class=ProductionTenantSeeder
```

### Check environment variables:
```bash
php artisan tinker
>>> env('AZURE_AD_CLIENT_ID')
>>> env('AZURE_AD_CLIENT_SECRET')
>>> env('AZURE_AD_REDIRECT_URI')
>>> env('AZURE_AD_TENANT_ID')
```

All should return values (not null).

## Contact
If issues persist after following all steps, check the detailed guide in:
- `AZURE-SSO-SIGNUP-ERROR-FIX.md`

