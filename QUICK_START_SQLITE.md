# Quick Start with SQLite (No PostgreSQL Setup Needed)

This will let you test the application immediately without installing PostgreSQL.

**Note**: Multi-tenant features won't work with SQLite, but you can see the UI and test basic functionality.

## Steps

### 1. Update .env to use SQLite

Edit `.env` and change:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Comment out PostgreSQL settings
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_USERNAME=postgres
# DB_PASSWORD=
```

### 2. Create SQLite Database File

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
```

### 3. Modify Middleware for SQLite Testing

Edit `app/Http/Middleware/IdentifyTenant.php` and replace the `handle` method with:

```php
public function handle(Request $request, Closure $next): Response
{
    // For SQLite testing - skip tenant check
    if (config('database.default') === 'sqlite') {
        // Create a dummy tenant object for testing
        $tenant = new \App\Models\Tenant([
            'id' => 1,
            'name' => 'Test Company',
            'database_name' => 'test',
            'database_host' => '127.0.0.1',
            'database_username' => 'test',
            'database_password' => '',
            'is_active' => true,
        ]);
        $request->merge(['tenant' => $tenant]);
        app()->instance('tenant', $tenant);
        return $next($request);
    }
    
    // Original code for PostgreSQL...
    $identification = config('tenant.identification', 'header');
    $tenant = null;

    if (app()->environment('local')) {
        $tenantId = $request->header(config('tenant.header_name', 'X-Tenant-ID'));
        
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        } else {
            $tenant = Tenant::where('is_active', true)->first();
        }
    } else {
        if ($identification === 'header') {
            $tenantId = $request->header(config('tenant.header_name', 'X-Tenant-ID'));
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
            }
        } elseif ($identification === 'subdomain') {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];
            $tenant = Tenant::where('domain', $subdomain)->first();
        } elseif ($identification === 'domain') {
            $host = $request->getHost();
            $tenant = Tenant::where('domain', $host)->first();
        }
    }

    if (!$tenant || !$tenant->is_active) {
        abort(404, 'Tenant not found or inactive. Please run: php artisan setup:database');
    }

    $request->merge(['tenant' => $tenant]);
    app()->instance('tenant', $tenant);

    return $next($request);
}
```

### 4. Modify SwitchTenantDatabase Middleware

Edit `app/Http/Middleware/SwitchTenantDatabase.php` and add at the start:

```php
public function handle(Request $request, Closure $next): Response
{
    // Skip database switching for SQLite
    if (config('database.default') === 'sqlite') {
        return $next($request);
    }
    
    // Original code...
    $tenant = $request->get('tenant');
    // ... rest of code
}
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Create Test User

```bash
php artisan tinker
```

Then run:
```php
\App\Models\Tenant::create([
    'id' => 1,
    'name' => 'Test Company',
    'database_name' => 'test',
    'database_host' => '127.0.0.1',
    'database_username' => 'test',
    'database_password' => '',
    'is_active' => true,
]);

\App\Models\User::create([
    'tenant_id' => 1,
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

### 7. Start Servers

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

### 8. Access Application

Open: http://localhost:8000

Login:
- Email: `admin@test.com`
- Password: `password`

---

## Limitations with SQLite

- ❌ Multi-tenant features won't work properly
- ❌ Some advanced PostgreSQL features unavailable
- ✅ You can see the UI
- ✅ You can test basic CRUD operations
- ✅ You can see the application structure

**For full functionality, you'll need PostgreSQL later.**

