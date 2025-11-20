# Starting Server with Laravel Herd (Easiest Solution!)

Since you have Laravel Herd installed, this is the **best way** to run your Laravel application on Windows.

## Step 1: Start Herd Desktop App

1. **Open Herd Desktop Application**
   - Press `Windows Key` and search for "Herd"
   - Or look in your Start Menu for "Laravel Herd"
   - Click to open the Herd desktop app

2. **Make sure Herd is running**
   - You should see the Herd icon in your system tray (bottom right)
   - If it's not running, start it from the Start Menu

## Step 2: Link Your Project

Once Herd is running, in your terminal:

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system
herd link accounting-system
```

Or Herd will automatically detect Laravel projects. You can also:
- Right-click the Herd tray icon
- Select "Sites"
- Your project should appear automatically

## Step 3: Access Your Application

Herd will automatically create a URL for your project:
- **http://accounting-system.test** (or similar)

Or check the Herd desktop app to see the exact URL.

## Step 4: Start Frontend (Still Needed)

You still need to run Vite for frontend assets:

```powershell
cd D:\Repository\SindbadTech\Accounting-System\accounting-system
npm run dev
```

## Benefits of Using Herd

✅ No port permission issues
✅ No need to run `php artisan serve`
✅ Automatic SSL certificates
✅ Works with multiple Laravel projects
✅ No firewall configuration needed

## If Herd Desktop Won't Start

1. Check if it's already running (system tray)
2. Try restarting your computer
3. Reinstall Herd from: https://herd.laravel.com/windows


