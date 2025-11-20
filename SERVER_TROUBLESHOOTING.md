# Server Troubleshooting Guide

If you're having trouble starting `php artisan serve`, try these solutions:

## Solution 1: Use 0.0.0.0 instead of 127.0.0.1

```bash
php artisan serve --host=0.0.0.0 --port=9000
```

This binds to all network interfaces, which sometimes works better on Windows.

Then access: **http://localhost:9000**

## Solution 2: Check Windows Firewall

Windows Firewall might be blocking PHP. Try:

**Option A: Temporarily disable firewall for testing**
1. Open Windows Security
2. Firewall & network protection
3. Turn off firewall temporarily (just for testing)

**Option B: Allow PHP through firewall**
1. Windows Security → Firewall & network protection
2. Allow an app through firewall
3. Find `php.exe` or add it manually:
   - Path: `C:\php\php.exe` (or wherever PHP is installed)
   - Check both Private and Public networks

## Solution 3: Run as Administrator

Sometimes ports require admin privileges:

1. Right-click PowerShell/Command Prompt
2. Select "Run as Administrator"
3. Navigate to project: `cd D:\Repository\SindbadTech\Accounting-System\accounting-system`
4. Run: `php artisan serve --host=0.0.0.0 --port=9000`

## Solution 4: Use XAMPP/WAMP instead

If `php artisan serve` continues to fail:

1. **Install XAMPP** (or WAMP/MAMP)
2. Point Apache to your Laravel `public` folder:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "D:/Repository/SindbadTech/Accounting-System/accounting-system/public"
       ServerName accounting.local
       <Directory "D:/Repository/SindbadTech/Accounting-System/accounting-system/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
3. Add to `hosts` file: `127.0.0.1 accounting.local`
4. Access: **http://accounting.local**

## Solution 5: Check Antivirus/Antimalware

Some antivirus software blocks local servers:

1. Temporarily disable antivirus
2. Try starting server
3. If it works, add exception for PHP/Laravel

## Solution 6: Check if port is already in use

```powershell
# Check what's using a specific port (e.g., 9000)
netstat -ano | findstr ":9000"

# If you see a PID, find the process:
tasklist | findstr "<PID_NUMBER>"

# Kill the process if needed:
taskkill /PID <PID_NUMBER> /F
```

## Solution 7: Use Laravel Valet (Windows alternative)

Install **Laravel Herd** (Windows equivalent of Valet):

1. Download: https://herd.laravel.com/windows
2. Install
3. Point to your project folder
4. Access: **http://accounting-system.test**

## Quick Test Commands

```bash
# Test if PHP is working
php -v

# Test if Laravel is installed
php artisan --version

# Try different ports
php artisan serve --host=0.0.0.0 --port=9000
php artisan serve --host=0.0.0.0 --port=8080
php artisan serve --host=0.0.0.0 --port=5000

# Check if server started
netstat -ano | findstr "LISTENING"
```

## Alternative: Use Built-in PHP Server Manually

```bash
cd public
php -S 0.0.0.0:9000
```

Then access: **http://localhost:9000**

---

## Still Having Issues?

Try these diagnostic steps:

1. **Check PHP error log**: Look in `php.ini` for error log location
2. **Check Laravel logs**: `storage/logs/laravel.log`
3. **Verify PHP extensions**: `php -m` (should show openssl, pdo, etc.)
4. **Test with minimal server**: `php -S 0.0.0.0:9000 -t public`

The most common fix is using `--host=0.0.0.0` instead of `127.0.0.1`!

