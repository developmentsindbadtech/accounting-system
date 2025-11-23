# DevOps - Quick Deployment Commands

## ⚡ Just Run These Commands (In Order)

```bash
# 1. Go to project directory
cd /path/to/accounting-system

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Run migrations
php artisan migrate --force

# 5. Create user accounts (THE IMPORTANT ONE)
php artisan users:create-sindbad-tech

# 6. Ensure tenant exists
php artisan db:seed --class=ProductionTenantSeeder

# 7. Clear caches
php artisan optimize:clear

# 8. Restart services (adjust based on your setup)
sudo systemctl restart php8.2-fpm  # or php-fpm
sudo systemctl restart nginx        # or apache2
```

---

## 🎯 Most Important Commands (Minimum)

If you're in a hurry, just run these:

```bash
git pull origin main
php artisan users:create-sindbad-tech
php artisan optimize:clear
```

---

## 📝 What Gets Created

After running `php artisan users:create-sindbad-tech`:

**Accountant Accounts:**
- revemar.surigao@sindbad.tech
- hazel.bacalso@sindbad.tech  
- aziz.alsultan@sindbad.tech
- mohammed.agbawi@sindbad.tech

**Admin Account:**
- development@sindbad.tech

**Password for ALL:** `Ksa@2021!`

---

## ✅ Verify It Worked

```bash
# Check users were created
php artisan tinker
>>> \App\Models\User::where('email', 'LIKE', '%@sindbad.tech')->count();
>>> exit
```

Should return `5` (4 accountants + 1 admin)

---

## 🔧 Troubleshooting

### Command not found?
```bash
# Make sure you pulled the latest code
git pull origin main

# Check if command exists
php artisan list | grep sindbad
```

### Need to update existing accounts?
```bash
# Just run the command again - it will update existing accounts
php artisan users:create-sindbad-tech
```

---

## 📞 That's It!

After running these commands:
- ✅ All accounts created
- ✅ Users can login via email/password
- ✅ Users can login via SSO (auto-created if new)
- ✅ No more "contact admin" errors
