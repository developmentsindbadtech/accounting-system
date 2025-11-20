# Quick Start Guide

## Before You Start

**You need PostgreSQL installed and running!**

If you see a connection error, PostgreSQL is not running or not installed.

### Option A: Install PostgreSQL (Recommended)

See `INSTALL_POSTGRESQL.md` for detailed installation instructions.

**Quick version:**
1. Download from: https://www.postgresql.org/download/windows/
2. Install (remember the password you set!)
3. Check if service is running: `Get-Service | Where-Object {$_.Name -like "*postgres*"}`
4. Start if needed: `Start-Service postgresql-x64-16` (replace with your version)

### Option B: Check PostgreSQL Status

Run the diagnostic script:
```powershell
.\check-postgres.ps1
```

This will tell you:
- If PostgreSQL is installed
- If the service is running
- What needs to be fixed

---

## Setup (One-Time)

### 1. Update .env

Make sure `.env` has correct PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password_here
```

### 2. Run Setup Command

```bash
php artisan setup:database
```

This will:
- ✅ Create databases automatically
- ✅ Run all migrations
- ✅ Create demo tenant
- ✅ Create admin user

**Login credentials will be shown:**
- Email: `admin@demo.com`
- Password: `password`

---

## Daily Development (Run Every Time)

You need **TWO terminals**:

### Terminal 1: Backend Server
```bash
cd accounting-system
php artisan serve
```
✅ Server starts at: `http://localhost:8000`

### Terminal 2: Frontend Assets (Vite)
```bash
cd accounting-system
npm run dev
```
✅ Vite watches for changes and compiles assets automatically

---

## Access Application

Open browser: **http://localhost:8000**

**Login:**
- Email: `admin@demo.com`
- Password: `password`

---

## Troubleshooting

### "Connection refused" or "PostgreSQL not found"

1. **Check if PostgreSQL is installed:**
   ```powershell
   .\check-postgres.ps1
   ```

2. **If not installed:** See `INSTALL_POSTGRESQL.md`

3. **If installed but not running:**
   ```powershell
   # Find service
   Get-Service | Where-Object {$_.Name -like "*postgres*"}
   
   # Start service (as Administrator)
   Start-Service postgresql-x64-16
   ```

4. **Verify .env credentials:**
   - Check `DB_PASSWORD` matches your PostgreSQL password
   - Check `DB_HOST` is `127.0.0.1`
   - Check `DB_PORT` is `5432`

### "Tenant not found" error

Run setup command again:
```bash
php artisan setup:database
```

### Frontend not loading

- Make sure `npm run dev` is running in Terminal 2
- Check browser console (F12) for errors
- Clear browser cache

---

## That's It!

Just remember:
1. **One-time setup**: `php artisan setup:database` (after PostgreSQL is installed)
2. **Daily**: Run `php artisan serve` and `npm run dev` in separate terminals
3. **Access**: http://localhost:8000
