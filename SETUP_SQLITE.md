# Quick Setup with SQLite (No PostgreSQL Needed!)

This lets you test the application **right now** without installing PostgreSQL.

## Quick Setup (2 minutes)

### Step 1: Switch to SQLite

Edit `.env` file and change these lines:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Comment out or remove PostgreSQL lines:
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_USERNAME=postgres
# DB_PASSWORD=
```

### Step 2: Create Database File

The file should already be created, but if not:

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Create Test Data

```bash
php artisan tinker
```

Then paste this:

```php
// Create tenant
\App\Models\Tenant::create([
    'id' => 1,
    'name' => 'Test Company',
    'database_name' => 'test',
    'database_host' => '127.0.0.1',
    'database_username' => 'test',
    'database_password' => '',
    'is_active' => true,
]);

// Create user
\App\Models\User::create([
    'tenant_id' => 1,
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

Type `exit` to leave tinker.

### Step 5: Start Servers

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

### Step 6: Open Application

Go to: **http://localhost:8000**

Login:
- Email: `admin@test.com`
- Password: `password`

---

## What You Can Test

✅ **UI and Navigation** - See all the pages and layouts
✅ **Chart of Accounts** - Create and manage accounts
✅ **Journal Entries** - Create journal entries
✅ **Customers & Invoices** - Basic CRUD operations
✅ **Vendors & Bills** - Basic CRUD operations
✅ **Dashboard** - See the interface

## Limitations

❌ **Multi-tenant features** - Won't work properly (single database)
❌ **Some advanced features** - May have issues
✅ **Everything else** - Should work fine for testing

---

## Switch Back to PostgreSQL Later

When you're ready for full functionality:

1. Install PostgreSQL (see `INSTALL_POSTGRESQL.md`)
2. Update `.env` back to PostgreSQL settings
3. Run `php artisan setup:database`

For now, SQLite is perfect to see what you have! 🚀

