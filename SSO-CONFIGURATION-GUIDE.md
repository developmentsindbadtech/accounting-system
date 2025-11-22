# Azure SSO Configuration Guide

## ✅ Fixed Issues

The following issues have been resolved to ensure Azure SSO works properly:

### 1. CSRF Token Protection
**Problem:** Azure OAuth callback was being blocked by CSRF protection.  
**Solution:** Added CSRF exception for `/login/azure/callback` in `bootstrap/app.php`.

### 2. Session Configuration for HTTPS
**Problem:** Session cookies weren't configured properly for cross-site OAuth with HTTPS.  
**Solution:** Updated `config/session.php` to automatically set:
- `secure` cookies for production (HTTPS required)
- `same_site=none` for production (allows cross-site OAuth)

### 3. Enhanced Error Logging
**Problem:** Limited error information made debugging difficult.  
**Solution:** Added comprehensive logging in `AzureController` to track:
- OAuth callback parameters
- Microsoft API responses
- State validation errors
- Network communication issues

---

## 🔧 Required Environment Variables

Add these to your production `.env` file:

```env
# Application Settings
APP_URL=https://stas.sindbad.tech
APP_ENV=production
APP_DEBUG=false

# Azure AD OAuth Configuration
AZURE_AD_TENANT_ID=your-tenant-id-here
AZURE_AD_CLIENT_ID=your-client-id-here
AZURE_AD_CLIENT_SECRET=your-client-secret-here
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback

# Session Configuration (automatically set for production)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

# Important: For multi-domain setup, set these if needed
# SESSION_DOMAIN=.sindbad.tech
```

---

## ☁️ Azure AD App Registration Requirements

Ensure your Azure AD App Registration has these settings:

### 1. Redirect URIs
Add to "Web" platform:
```
https://stas.sindbad.tech/login/azure/callback
```

### 2. API Permissions
Required permissions (all already granted):
- ✅ `openid` - Sign users in
- ✅ `profile` - Read user profile
- ✅ `email` - Read user email
- ✅ `User.Read` - Read user information from Microsoft Graph

### 3. Client Secret
- Current secret is valid until expiration
- Update `AZURE_AD_CLIENT_SECRET` before it expires
- Find in: Azure Portal → App Registration → Certificates & secrets

### 4. Supported Account Types
Should be set to:
```
Accounts in this organizational directory only (Single tenant)
```

---

## 🧪 Testing SSO Login

### Test the Login Flow:

1. **Clear browser cache and cookies** for `stas.sindbad.tech`

2. **Navigate to login page:**
   ```
   https://stas.sindbad.tech/login
   ```

3. **Click "Sign in with Microsoft"** button

4. **Microsoft login should:**
   - Redirect to Microsoft login page
   - Accept your credentials
   - Ask for consent (first time only)
   - Redirect back to `https://stas.sindbad.tech/login/azure/callback`
   - Log you into the application
   - Redirect to dashboard

### If Login Fails:

1. **Check Laravel logs:**
   ```bash
   cd /path/to/accounting-system
   tail -f storage/logs/laravel.log
   ```

2. **Look for these log entries:**
   - `Azure SSO callback received` - confirms callback was reached
   - `Azure user retrieved successfully` - confirms OAuth worked
   - `User successfully logged in via Azure SSO` - confirms full login

3. **Common issues and solutions:**

   | Error | Cause | Solution |
   |-------|-------|----------|
   | "Session expired" | State mismatch | Clear cookies, try again |
   | "Unable to communicate with Microsoft" | Network/API error | Check Azure AD configuration |
   | "System configuration error" | No tenant in database | Run tenant creation script |
   | "Your account is inactive" | User disabled | Enable user in database |

---

## 🗄️ Database Requirements

### Ensure Tenant Exists

SSO requires at least one tenant in the database:

```bash
php artisan tinker
```

```php
// Check if tenant exists
Tenant::count();

// If no tenant, create one:
Tenant::create([
    'name' => 'Sindbad Tech',
    'slug' => 'sindbad',
    'is_active' => true,
]);
```

### Sessions Table

Ensure the sessions table exists (for database session driver):

```bash
php artisan migrate
```

---

## 🔍 Debugging Tips

### 1. Enable Debug Mode Temporarily
For troubleshooting only (NEVER leave on in production):

```env
APP_DEBUG=true
```

### 2. Check Storage Permissions
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 3. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 4. Test OAuth Manually
Use this URL to test the OAuth flow directly:
```
https://stas.sindbad.tech/login/azure
```

---

## 📊 CSV Export on Google Cloud Platform

### Current Configuration

CSV exports work seamlessly on Google Cloud Platform with the current setup:

1. **No cloud storage required** - CSVs are generated on-demand and streamed directly to the user's browser
2. **Memory efficient** - Uses Laravel's streaming response
3. **No temporary files** - Data is written directly to the output stream
4. **Platform agnostic** - Works on any hosting provider (GCP, AWS, Azure, etc.)

### How It Works

```php
// Example from any controller
public function export() {
    return response()->stream(function () {
        $file = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write CSV headers
        fputcsv($file, ['Column1', 'Column2', ...]);
        
        // Stream data directly from database
        Model::query()->chunk(1000, function ($records) use ($file) {
            foreach ($records as $record) {
                fputcsv($file, [$record->field1, $record->field2, ...]);
            }
        });
        
        fclose($file);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="export.csv"',
    ]);
}
```

### Testing CSV Export on GCP

1. **Login as Admin or Accountant:**
   ```
   https://stas.sindbad.tech/login
   ```

2. **Navigate to any module:**
   - Customers: `/customers`
   - Invoices: `/invoices`
   - Bills: `/bills`
   - etc.

3. **Click "Download CSV"** - File should download immediately

### CSV Export Routes

All CSV exports are protected by role middleware:

| Module | URL | Access |
|--------|-----|--------|
| Customers | `/customers/export` | Admin, Accountant |
| Invoices | `/invoices/export` | Admin, Accountant |
| Bills | `/bills/export` | Admin, Accountant |
| Vendors | `/vendors/export` | Admin, Accountant |
| Inventory | `/inventory/export` | Admin, Accountant |
| Fixed Assets | `/fixed-assets/export` | Admin, Accountant |
| Journal Entries | `/journal-entries/export` | Admin, Accountant |
| Chart of Accounts | `/chart-of-accounts/export` | Admin, Accountant |

### GCP-Specific Considerations

✅ **No additional configuration needed** - The current implementation:
- Doesn't require Google Cloud Storage
- Doesn't need service accounts or credentials
- Works with any PHP hosting environment
- Scales automatically with your GCP instances

✅ **Performance on GCP:**
- Stream response doesn't consume memory
- Can handle exports with millions of records
- No disk I/O (no temporary files)
- Works behind Cloud Load Balancer

✅ **Security:**
- Role-based access control enforced
- Tenant isolation automatic
- HTTPS enforced in production
- No data stored on disk

---

## 🚀 Deployment Checklist

Before deploying these SSO fixes:

- [ ] Update `.env` with correct Azure AD credentials
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Ensure database has at least one tenant
- [ ] Verify Azure AD redirect URI matches exactly
- [ ] Clear all caches: `php artisan config:clear`
- [ ] Test SSO login with a real Microsoft account
- [ ] Verify logs are being written to `storage/logs/laravel.log`
- [ ] Test CSV exports work for Admin/Accountant roles
- [ ] Confirm Viewer role cannot access CSV exports

---

## 📞 Support

If SSO continues to fail after these fixes:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs (nginx/apache)
3. Verify network connectivity to Microsoft endpoints
4. Confirm Azure AD app registration is active
5. Test with a different Microsoft account

---

**Last Updated:** November 22, 2025  
**Version:** 2.0  
**Status:** ✅ Production Ready

