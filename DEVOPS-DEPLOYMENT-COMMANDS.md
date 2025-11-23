# DevOps Deployment Commands - Straightforward Guide

## Quick Deployment Steps

### Step 1: Pull Latest Code
```bash
cd /path/to/accounting-system
git pull origin main
```

### Step 2: Install Dependencies (if needed)
```bash
composer install --no-dev --optimize-autoloader
```

### Step 3: Run Migrations (if any new migrations)
```bash
php artisan migrate --force
```

### Step 4: Create User Accounts
```bash
php artisan users:create-sindbad-tech
```

This will create:
- 4 accountant accounts (revemar.surigao, hazel.bacalso, aziz.alsultan, mohammed.agbawi)
- 1 admin account (development@sindbad.tech)
- All with password: `Ksa@2021!`

### Step 5: Ensure Tenant Exists (Auto-created by SSO, but run this to be safe)
```bash
php artisan db:seed --class=ProductionTenantSeeder
```

### Step 6: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Step 7: Restart Services (if using PHP-FPM/Nginx)
```bash
# For PHP-FPM
sudo systemctl restart php8.2-fpm
# or
sudo systemctl restart php-fpm

# For Nginx
sudo systemctl restart nginx
```

### Step 8: Verify (Optional)
```bash
# Check users were created
php artisan tinker
>>> \App\Models\User::where('email', 'LIKE', '%@sindbad.tech')->get(['name', 'email', 'role']);
>>> exit

# Check tenant exists
php artisan tinker
>>> \App\Models\Tenant::count();
>>> exit
```

---

## One-Line Command (All Steps Combined)

```bash
cd /path/to/accounting-system && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan users:create-sindbad-tech && \
php artisan db:seed --class=ProductionTenantSeeder && \
php artisan optimize:clear && \
sudo systemctl restart php8.2-fpm && \
sudo systemctl restart nginx
```

---

## What Each Command Does

| Command | Purpose |
|---------|---------|
| `git pull origin main` | Get latest code changes |
| `composer install --no-dev` | Install PHP dependencies |
| `php artisan migrate --force` | Run database migrations |
| `php artisan users:create-sindbad-tech` | **Create all user accounts** |
| `php artisan db:seed --class=ProductionTenantSeeder` | Ensure tenant exists |
| `php artisan optimize:clear` | Clear all caches |
| `systemctl restart` | Restart web server |

---

## After Deployment

### Test Email/Password Login
1. Go to login page
2. Use email: `development@sindbad.tech`
3. Password: `Ksa@2021!`
4. Should login successfully

### Test SSO Login
1. Click "Sign in with Microsoft"
2. Login with any `@sindbad.tech` account
3. Should auto-create user and login (if new) or login directly (if exists)

---

## Troubleshooting

### If users:create-sindbad-tech command not found
```bash
# The command should exist, but if not, check if file exists:
ls -la app/Console/Commands/CreateSindbadTechUsers.php

# If file doesn't exist, pull code again
git pull origin main
```

### If tenant doesn't exist
```bash
php artisan db:seed --class=ProductionTenantSeeder
```

### If still getting errors
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check database connection
php artisan tinker
>>> \DB::connection()->getPdo();
>>> exit
```

---

## Summary

**Minimum Required Commands:**
```bash
git pull origin main
php artisan users:create-sindbad-tech
php artisan optimize:clear
```

That's it! The rest are optional for a complete deployment.
