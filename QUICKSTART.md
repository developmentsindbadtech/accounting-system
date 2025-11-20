# Quick Start Guide - Sindbad.Tech Accounting System

## Step-by-Step Setup (5 minutes)

### 1. Install Dependencies

```bash
cd accounting-system
composer install
npm install
```

### 2. Configure Environment

```bash
# Copy .env file (if not exists)
copy .env.example .env  # Windows
# OR
cp .env.example .env    # Linux/Mac

# Edit .env and set your database credentials
# Then generate app key
php artisan key:generate
```

Update `.env` with your PostgreSQL credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Create Database

```sql
-- Connect to PostgreSQL
CREATE DATABASE accounting_central;
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Create Test Tenant and User

Run this in `php artisan tinker`:

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

// Create tenant database manually first:
// CREATE DATABASE tenant_test;

// Then create user
\App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

### 6. Modify Middleware for Local Testing

Temporarily modify `app/Http/Middleware/IdentifyTenant.php` to set a default tenant for local development:

```php
public function handle(Request $request, Closure $next): Response
{
    // TEMPORARY: For local development
    if (app()->environment('local')) {
        $tenant = Tenant::find(1); // Use your tenant ID
        if ($tenant && $tenant->is_active) {
            $request->merge(['tenant' => $tenant]);
            app()->instance('tenant', $tenant);
            return $next($request);
        }
    }
    
    // ... rest of the code
}
```

### 7. Run Tenant Database Migrations

You need to run migrations on the tenant database. Create a temporary command or manually:

```bash
# Set tenant database connection in .env temporarily
# DB_DATABASE=tenant_test
# Then run migrations
php artisan migrate
```

### 8. Start Development Servers

**Terminal 1 (Backend):**
```bash
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
npm run dev
```

### 9. Access Application

Open browser: `http://localhost:8000`

Login with:
- Email: `admin@test.com`
- Password: `password`

## Troubleshooting

**"Tenant not found" error?**
- Make sure you modified the IdentifyTenant middleware for local testing
- Or use a browser extension to add `X-Tenant-ID: 1` header

**Database connection error?**
- Check PostgreSQL is running
- Verify credentials in `.env`
- Ensure database exists

**Frontend not loading?**
- Make sure `npm run dev` is running
- Check browser console for errors

