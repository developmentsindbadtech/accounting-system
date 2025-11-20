# Quick Setup Guide

## Option 1: Automated Setup (Recommended)

### Step 1: Create Databases

**Windows:**
```powershell
.\setup.ps1
```

Or if PowerShell scripts are blocked:
```cmd
setup.bat
```

**Manual (if scripts don't work):**
1. Open PostgreSQL client (pgAdmin, DBeaver, or psql)
2. Run these SQL commands:
```sql
CREATE DATABASE accounting_central;
CREATE DATABASE tenant_demo;
```

### Step 2: Run Laravel Setup

```bash
php artisan setup:database
```

This will:
- ✅ Verify databases exist
- ✅ Run all migrations
- ✅ Create demo tenant
- ✅ Create admin user (admin@demo.com / password)

### Step 3: Start Servers

**Terminal 1 (Backend):**
```bash
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
npm run dev
```

### Step 4: Access Application

Open: http://localhost:8000

**Login:**
- Email: `admin@demo.com`
- Password: `password`

---

## Option 2: Manual Setup

If the automated setup doesn't work:

### 1. Create Databases Manually

Using pgAdmin, DBeaver, or psql command line:

```sql
CREATE DATABASE accounting_central;
CREATE DATABASE tenant_demo;
```

### 2. Update .env File

Make sure your `.env` has:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Create Tenant and User

```bash
php artisan tinker
```

Then run:
```php
$tenant = \App\Models\Tenant::create([
    'name' => 'Demo Company',
    'database_name' => 'tenant_demo',
    'database_host' => '127.0.0.1',
    'database_username' => 'postgres',
    'database_password' => 'your_password',
    'is_active' => true,
]);

// Switch to tenant database temporarily
// Update .env: DB_DATABASE=tenant_demo
// Run: php artisan migrate
// Change back: DB_DATABASE=accounting_central

\App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin',
    'email' => 'admin@demo.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

### 5. Start Servers

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

---

## Troubleshooting

**"Tenant not found" error?**
- Make sure you ran `php artisan setup:database`
- Check that tenant exists: `php artisan tinker` then `\App\Models\Tenant::first()`

**Database connection error?**
- Verify PostgreSQL is running
- Check `.env` credentials
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

**Frontend not loading?**
- Make sure `npm run dev` is running
- Check browser console for errors
- Clear browser cache

