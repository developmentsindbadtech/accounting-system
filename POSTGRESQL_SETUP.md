# PostgreSQL Setup Guide

## Check if PostgreSQL is Running

### Option 1: Check Windows Services

Open PowerShell as Administrator and run:

```powershell
Get-Service | Where-Object {$_.Name -like "*postgres*" -or $_.DisplayName -like "*postgres*"}
```

### Option 2: Check via Services GUI

1. Press `Win + R`
2. Type `services.msc` and press Enter
3. Look for services like:
   - `postgresql-x64-XX` (where XX is version number)
   - `PostgreSQL Server`
   - `postgresql`

## Start PostgreSQL Service

### Option 1: Via PowerShell (Run as Administrator)

```powershell
# List PostgreSQL services
Get-Service | Where-Object {$_.Name -like "*postgres*"}

# Start the service (replace with actual service name)
Start-Service postgresql-x64-16
# OR
Start-Service postgresql-x64-15
# OR
Start-Service postgresql-x64-14
```

### Option 2: Via Services GUI

1. Open `services.msc`
2. Find PostgreSQL service
3. Right-click → Start

### Option 3: Via Command Line (Run as Administrator)

```cmd
net start postgresql-x64-16
```

Replace `postgresql-x64-16` with your actual PostgreSQL service name.

## Install PostgreSQL (If Not Installed)

1. Download from: https://www.postgresql.org/download/windows/
2. Run the installer
3. During installation:
   - Remember the **port** (default: 5432)
   - Set **superuser password** (for user `postgres`)
   - Install `pgAdmin` if you want a GUI tool

## Verify Connection

After starting PostgreSQL, test the connection:

```powershell
# Test with psql
psql -U postgres -h 127.0.0.1 -p 5432

# If prompted for password, enter your PostgreSQL password
```

Or test with Laravel:

```bash
php artisan tinker
```

Then in tinker:
```php
DB::connection()->getPdo();
```

If it connects successfully, you'll see no errors.

## Configure .env File

Make sure your `.env` has correct PostgreSQL settings:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password_here
```

**Important**: Replace `your_postgres_password_here` with your actual PostgreSQL superuser password.

## Alternative: Use SQLite for Quick Testing

If you want to quickly test without PostgreSQL, you can temporarily use SQLite:

1. Update `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
# Comment out PostgreSQL settings
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_USERNAME=postgres
# DB_PASSWORD=
```

2. Create the SQLite database:
```bash
touch database/database.sqlite
```

3. Run migrations:
```bash
php artisan migrate
```

**Note**: Multi-tenant features require PostgreSQL. Use SQLite only for initial testing.

