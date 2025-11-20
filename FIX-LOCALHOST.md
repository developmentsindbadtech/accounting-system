# Fix: Can't Run on Localhost

## Quick Fix (Try This First!)

Run this script in your terminal:

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system
.\start-localhost.ps1
```

This script will:
- ✅ Check if everything is set up correctly
- ✅ Find an available port
- ✅ Try multiple methods to start the server
- ✅ Show you the exact URL to access

## Common Issues & Solutions

### Issue 1: Port Permission Error

**Error:** `Failed to listen on 127.0.0.1:8000 (reason: An attempt was made to access a socket...)`

**Solution:**
1. **Run PowerShell as Administrator:**
   - Right-click PowerShell
   - Select "Run as Administrator"
   - Navigate to project: `cd D:\Repository\SindbadTech\Accounting-System\accounting-system`
   - Run: `php artisan serve --host=127.0.0.1 --port=3000`

2. **Or use Laravel Herd (Easiest!):**
   - Open Herd Desktop app
   - Run: `herd link accounting-system`
   - Access at: `http://accounting-system.test`

### Issue 2: Port Already in Use

**Error:** Port 8000 (or other) is already in use

**Solution:**
```powershell
# Find what's using the port
netstat -ano | findstr ":8000"

# Kill the process (replace PID with actual number)
taskkill /PID <PID_NUMBER> /F

# Or use a different port
php artisan serve --port=3000
```

### Issue 3: Windows Firewall Blocking

**Solution:**
1. Open Windows Security
2. Firewall & network protection
3. Allow an app through firewall
4. Add PHP: `C:\Users\kiosk\.config\herd\bin\php.exe`
5. Check both Private and Public

### Issue 4: Database Connection Error

**Error:** Can't connect to PostgreSQL

**Solution:**
1. Check PostgreSQL is running:
   ```powershell
   Get-Service | Where-Object {$_.Name -like "*postgres*"}
   ```

2. Start PostgreSQL if needed:
   ```powershell
   Start-Service postgresql-x64-16
   ```

3. Verify `.env` has correct database credentials

## Step-by-Step: Get It Running

### Method 1: Using Laravel Herd (Recommended)

1. **Start Herd Desktop App**
   - Search for "Herd" in Start Menu
   - Open the application

2. **Link Your Project**
   ```powershell
   cd D:\Repository\SindbadTech\Accounting-System\accounting-system
   herd link accounting-system
   ```

3. **Access Your App**
   - Open: `http://accounting-system.test`
   - Or check Herd app for the exact URL

4. **Start Frontend (Still Needed)**
   ```powershell
   npm run dev
   ```

### Method 2: Using php artisan serve

1. **Run PowerShell as Administrator**

2. **Navigate to Project**
   ```powershell
   cd D:\Repository\SindbadTech\Accounting-System\accounting-system
   ```

3. **Start Server**
   ```powershell
   php artisan serve --host=127.0.0.1 --port=3000
   ```

4. **Access Your App**
   - Open: `http://localhost:3000`

5. **Start Frontend (Separate Terminal)**
   ```powershell
   npm run dev
   ```

### Method 3: Using PHP Built-in Server

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system\public
php -S 127.0.0.1:3000
```

Then access: `http://localhost:3000`

## Still Not Working?

### Check These:

1. **Is PHP working?**
   ```powershell
   php -v
   ```

2. **Is Laravel working?**
   ```powershell
   php artisan --version
   ```

3. **Is .env configured?**
   ```powershell
   # Check if .env exists
   Test-Path .env
   
   # If not, create it
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. **Are dependencies installed?**
   ```powershell
   composer install
   npm install
   ```

5. **Try restarting your computer**
   - Sometimes Windows port reservations need a restart

## Need More Help?

Check these files:
- `SERVER_TROUBLESHOOTING.md` - More detailed server issues
- `START.md` - Quick start guide
- `START-WITH-HERD.md` - Using Laravel Herd

