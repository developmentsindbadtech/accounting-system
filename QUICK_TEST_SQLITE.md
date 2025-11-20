# Quick Test Setup with SQLite

**Note**: This is for quick testing only. Multi-tenant features require PostgreSQL.

## Steps

1. Update `.env` to use SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
# Comment out PostgreSQL settings temporarily
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_USERNAME=postgres
# DB_PASSWORD=
```

2. Create SQLite database file:

```bash
touch database/database.sqlite
```

Or on Windows PowerShell:
```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
```

3. Modify middleware temporarily to skip tenant check:

Edit `app/Http/Middleware/IdentifyTenant.php` and add this at the start:

```php
public function handle(Request $request, Closure $next): Response
{
    // TEMPORARY: Skip tenant check for SQLite testing
    if (config('database.default') === 'sqlite') {
        // Create a dummy tenant for testing
        $tenant = new \App\Models\Tenant([
            'id' => 1,
            'name' => 'Test Tenant',
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
    
    // ... rest of existing code
}
```

4. Run migrations:

```bash
php artisan migrate
```

5. Create a user manually:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'tenant_id' => 1,
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_active' => true,
]);
```

6. Start servers:

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

---

**Remember**: Switch back to PostgreSQL for full multi-tenant functionality!

