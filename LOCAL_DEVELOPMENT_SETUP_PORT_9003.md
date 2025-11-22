# Local Development Setup - Port 9003

## Quick Setup Guide for Running on http://localhost:9003

---

## 1. Update Your `.env` File

Open `.env` in the project root and update these settings:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9003

# Database Configuration (adjust as needed)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Azure AD Settings for LOCAL Development
AZURE_AD_CLIENT_ID=467e4e93-dfe0-48d7-9a5d-697aeeb5b7b0
AZURE_AD_CLIENT_SECRET=your_client_secret_here
AZURE_AD_REDIRECT_URI=http://localhost:9003/login/azure/callback
AZURE_AD_TENANT_ID=e60d4ac4-0106-4e11-8bdf-5715d69670b1

SESSION_DRIVER=database
CACHE_DRIVER=file
```

**Key Changes:**
- `APP_URL=http://localhost:9003`
- `AZURE_AD_REDIRECT_URI=http://localhost:9003/login/azure/callback`

---

## 2. Register Localhost URL in Azure AD

**IMPORTANT:** You must add the localhost callback URL to your Azure AD app registration.

### Steps:
1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** → **App registrations**
3. Find your app with Client ID: `467e4e93-dfe0-48d7-9a5d-697aeeb5b7b0`
4. Click on **Authentication** in the left sidebar
5. Under **Redirect URIs**, click **+ Add URI**
6. Enter: `http://localhost:9003/login/azure/callback`
7. Make sure the platform is set to **Web** (not SPA)
8. Click **Save**

**Note:** You can have multiple redirect URIs registered:
- `https://stas.sindbad.tech/login/azure/callback` (Production)
- `http://localhost:9003/login/azure/callback` (Local Development)

Both can coexist - the app will use whichever one is in your `.env` file.

---

## 3. Clear Laravel Caches

After updating `.env`, clear all caches:

```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 4. Start Development Servers

You need **TWO terminals** open:

### Terminal 1 - Laravel Backend (Port 9003)

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system
php artisan serve --port=9003
```

**Expected Output:**
```
   INFO  Server running on [http://127.0.0.1:9003].

  Press Ctrl+C to stop the server
```

### Terminal 2 - Vite Frontend

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system
npm run dev
```

**Expected Output:**
```
  VITE v5.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  press h + enter to show help
```

**Note:** Vite runs on port 5173 by default, but it proxies to your Laravel app.

---

## 5. Access Your Application

Open your browser and go to:

```
http://localhost:9003
```

**❌ DO NOT USE:**
- ~~https://stas.sindbad.tech~~ (That's production!)
- ~~http://localhost:8000~~ (Wrong port)

**✅ USE THIS:**
- `http://localhost:9003` ✓

---

## 6. Test Azure Login

1. Navigate to `http://localhost:9003/login`
2. Click **"Sign in with Microsoft"** button
3. You'll be redirected to Microsoft login
4. After authenticating, you'll be redirected back to `http://localhost:9003/login/azure/callback`
5. If successful, you'll be logged in and redirected to the dashboard

---

## 7. Test CSV Downloads

Once logged in as **Admin** or **Accountant**:

### Test Pages:
- `http://localhost:9003/customers` → Click "Download CSV"
- `http://localhost:9003/vendors` → Click "Download CSV"
- `http://localhost:9003/invoices` → Click "Download CSV"
- `http://localhost:9003/bills` → Click "Download CSV"
- `http://localhost:9003/inventory` → Click "Download CSV"
- `http://localhost:9003/fixed-assets` → Click "Download CSV"
- `http://localhost:9003/chart-of-accounts` → Click "Download CSV"
- `http://localhost:9003/journal-entries` → Click "Download CSV"
- `http://localhost:9003/dashboard` → Click "Download CSV"
- `http://localhost:9003/logs` → Click "Download CSV"
- `http://localhost:9003/reports/trial-balance` → Click "Download CSV"
- `http://localhost:9003/reports/profit-loss` → Click "Download CSV"
- `http://localhost:9003/reports/balance-sheet` → Click "Download CSV"

### Expected Behavior:
- **Admin/Accountant:** Green "Download CSV" button → Downloads file
- **Viewer:** Grayed out "Download CSV" button → Cannot click

---

## Troubleshooting

### Issue: "Server running on [http://127.0.0.1:8000]" instead of port 9003

**Solution:** Make sure you're using the `--port=9003` flag:
```powershell
php artisan serve --port=9003
```

---

### Issue: "Port 9003 is already in use"

**Solution:** Kill the existing process:

**Windows PowerShell:**
```powershell
# Find the process using port 9003
netstat -ano | findstr :9003

# Kill the process (replace PID with the actual Process ID)
taskkill /PID <PID> /F
```

---

### Issue: "XSRF token mismatch" or "419 Page Expired"

**Solution:** Clear browser cookies and Laravel session:
```powershell
php artisan session:clear
```

Then refresh the page with `Ctrl+Shift+R`

---

### Issue: "Tenant not found or inactive"

**Solution:** You need to create a tenant and user. Run:

```powershell
php artisan tinker
```

Then:
```php
// Create tenant
$tenant = \App\Models\Tenant::create([
    'name' => 'Test Company',
    'database_name' => 'tenant_test',
    'database_host' => '127.0.0.1',
    'database_username' => 'postgres',
    'database_password' => 'your_password',
    'is_active' => true,
]);

// Create admin user
\App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

---

### Issue: Azure callback returns 500 error

**Possible causes:**
1. Redirect URI not registered in Azure AD
2. Wrong redirect URI in `.env`
3. Database connection issues
4. Tenant not found

**Check logs:**
```powershell
tail storage/logs/laravel.log
```

---

### Issue: "Call to undefined method App\Models\User::canEdit()"

**Solution:** The `canEdit()` method should already exist in `app/Models/User.php`. If not, check the file.

---

## Quick Reference Commands

### Start Servers:
```powershell
# Backend (Port 9003)
php artisan serve --port=9003

# Frontend
npm run dev
```

### Clear Caches:
```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Run Migrations:
```powershell
php artisan migrate
```

### View Routes:
```powershell
php artisan route:list
```

### Check Current Config:
```powershell
php artisan config:show app.url
```

---

## Environment Variables Checklist

- [x] `APP_URL=http://localhost:9003`
- [x] `APP_ENV=local`
- [x] `APP_DEBUG=true`
- [x] `AZURE_AD_REDIRECT_URI=http://localhost:9003/login/azure/callback`
- [x] Database credentials configured
- [x] Azure AD Client ID and Secret configured

---

## Testing CSV Download Permissions

### As Admin:
1. Login as admin user
2. Navigate to any list page (customers, invoices, etc.)
3. You should see a **green "Download CSV"** button
4. Click it - CSV file should download

### As Accountant:
1. Login as accountant user
2. Should have same permissions as admin
3. Can download all CSV files

### As Viewer:
1. Login as viewer user
2. You should see a **grayed-out "Download CSV"** button
3. Hovering shows "Viewer: No permission" tooltip
4. Cannot click the button
5. If you manually visit `/customers/export`, you should get **403 Forbidden**

---

## URLs to Remember

- **Application:** `http://localhost:9003`
- **Login:** `http://localhost:9003/login`
- **Dashboard:** `http://localhost:9003/dashboard`
- **Customers:** `http://localhost:9003/customers`
- **Azure Login:** `http://localhost:9003/login/azure`
- **Azure Callback:** `http://localhost:9003/login/azure/callback`

---

## Production vs Local

| Setting | Local (Development) | Production |
|---------|-------------------|------------|
| URL | `http://localhost:9003` | `https://stas.sindbad.tech` |
| Azure Redirect | `http://localhost:9003/login/azure/callback` | `https://stas.sindbad.tech/login/azure/callback` |
| APP_ENV | `local` | `production` |
| APP_DEBUG | `true` | `false` |
| Port | `9003` | `80/443` |

---

**Date Created:** November 21, 2025  
**Port:** 9003  
**Status:** ✅ Ready for Local Development

