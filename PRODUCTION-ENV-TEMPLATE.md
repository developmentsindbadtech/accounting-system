# Production Environment Configuration Template

Copy this configuration to your `.env` file on the production server.

```env
# =================================================
# PRODUCTION ENVIRONMENT CONFIGURATION
# Sindbad Tech Accounting System
# =================================================

# Application
APP_NAME="Sindbad Accounting"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE_GENERATE_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://stas.sindbad.tech
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_system
DB_USERNAME=postgres
DB_PASSWORD=your_secure_database_password

# Session Configuration (Critical for Azure SSO)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
# For production HTTPS with OAuth, these are automatically set:
# SESSION_SECURE_COOKIE=true (auto-set for production)
# SESSION_SAME_SITE=none (auto-set for production to allow OAuth)

# Azure AD Single Sign-On (SSO) Configuration
# Get these values from Azure Portal > App Registrations
# DevOps will provide the actual values in the .env file
AZURE_AD_TENANT_ID=your-tenant-id-here
AZURE_AD_CLIENT_ID=your-client-id-here
AZURE_AD_CLIENT_SECRET=your-client-secret-here
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback

# Cache Configuration
CACHE_STORE=database
CACHE_PREFIX=

# Queue Configuration
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Broadcasting
BROADCAST_CONNECTION=log

# Filesystem
FILESYSTEM_DISK=local

# Mail Configuration (if needed)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Livewire
LIVEWIRE_UPDATE_MODE=defer

# Vite (Frontend Assets)
VITE_APP_NAME="${APP_NAME}"
```

---

## ⚙️ Setup Instructions

### 1. Copy to Production Server

```bash
# On your production server
cd /path/to/accounting-system
nano .env
```

Paste the above configuration and update the values.

### 2. Generate Application Key

```bash
php artisan key:generate
```

This will automatically update the `APP_KEY` in your `.env` file.

### 3. Update Database Credentials

Replace these with your actual database credentials:
```env
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_db_user
DB_PASSWORD=your_actual_db_password
```

### 4. Verify Azure AD Configuration

Ensure these values match your Azure AD App Registration (DevOps will provide actual values):
```env
AZURE_AD_TENANT_ID=your-tenant-id-here
AZURE_AD_CLIENT_ID=your-client-id-here
AZURE_AD_CLIENT_SECRET=your-client-secret-here
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback
```

### 5. Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Run Migrations

```bash
php artisan migrate --force
```

### 8. Verify Tenant Exists

```bash
php artisan tinker
```

```php
// Check tenant
Tenant::count();

// If no tenant exists, create one:
Tenant::create(['name' => 'Sindbad Tech', 'slug' => 'sindbad', 'is_active' => true]);
exit
```

### 9. Test SSO Login

Navigate to:
```
https://stas.sindbad.tech/login
```

Click "Sign in with Microsoft" and verify the login flow works.

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` (NEVER `true` in production)
- [ ] `APP_ENV=production`
- [ ] Strong database password set
- [ ] `.env` file permissions: `chmod 600 .env`
- [ ] `.env` file not in git repository
- [ ] Azure AD client secret is current and valid
- [ ] HTTPS is enforced on the web server
- [ ] Firewall rules allow only necessary ports

---

## 🧪 Testing Checklist

After deployment:

- [ ] Can access homepage: `https://stas.sindbad.tech`
- [ ] Can access login page: `https://stas.sindbad.tech/login`
- [ ] SSO redirect works: Click "Sign in with Microsoft"
- [ ] SSO callback works: Microsoft redirects back successfully
- [ ] Can login with Microsoft account
- [ ] Dashboard loads after login
- [ ] Can export CSV (as Admin/Accountant)
- [ ] Viewer role cannot export CSV
- [ ] Logs are being written: `tail -f storage/logs/laravel.log`

---

## 📝 Important Notes

### Session Configuration

The session configuration has been updated to automatically handle production HTTPS:

- **Secure cookies**: Automatically enabled for `APP_ENV=production`
- **SameSite=none**: Automatically set for production to allow cross-site OAuth
- **Database driver**: Sessions stored in database for scalability

### CSV Export on GCP

CSV exports work out-of-the-box on Google Cloud Platform:

- ✅ No Google Cloud Storage configuration needed
- ✅ No service accounts or credentials required
- ✅ Streams directly to browser (memory efficient)
- ✅ Works with any hosting provider (GCP, AWS, Azure, etc.)
- ✅ Handles large datasets without memory issues

### Azure SSO

The following fixes ensure Azure SSO works correctly:

1. **CSRF Exception**: `/login/azure/callback` excluded from CSRF verification
2. **Session Security**: Cookies configured for HTTPS cross-site OAuth
3. **Error Handling**: Comprehensive logging and user-friendly error messages
4. **State Validation**: Proper handling of OAuth state parameter

---

**Last Updated:** November 22, 2025  
**Status:** ✅ Ready for Production Deployment

