# Stop Running Local Servers

## Quick Commands to Stop Servers

### Stop PHP/Laravel Server

**Option 1: Find and Kill PHP Processes (Windows PowerShell)**
```powershell
# Find all PHP processes
Get-Process | Where-Object {$_.ProcessName -like "*php*"}

# Stop all PHP processes
Get-Process | Where-Object {$_.ProcessName -like "*php*"} | Stop-Process -Force
```

**Option 2: Using Command Prompt**
```cmd
# Find PHP processes
tasklist | findstr php

# Stop PHP processes (replace <PID> with actual process ID)
taskkill /F /IM php.exe
```

**Option 3: Stop Specific Port**
```powershell
# Find what's using port 8000
netstat -ano | findstr ":8000"

# This will show: TCP    0.0.0.0:8000    0.0.0.0:0    LISTENING    12345
# The last number (12345) is the PID

# Kill that process
taskkill /PID 12345 /F
```

### Stop All Common Development Servers

**PowerShell Script (Run All at Once):**
```powershell
# Stop PHP servers
Get-Process | Where-Object {$_.ProcessName -like "*php*"} | Stop-Process -Force

# Stop Node/npm servers (if running)
Get-Process | Where-Object {$_.ProcessName -like "*node*"} | Stop-Process -Force

# Check if ports are free
netstat -ano | findstr ":8000 :3000 :8080 :9000"
```

### Stop Laravel Artisan Serve

If you started it with `php artisan serve`:

**Option 1: Press Ctrl+C** in the terminal where it's running

**Option 2: If you can't find the terminal:**
```powershell
# Kill all PHP processes (this will stop artisan serve)
Get-Process | Where-Object {$_.ProcessName -like "*php*"} | Stop-Process -Force
```

### Stop npm/vite Dev Server

**Option 1: Press Ctrl+C** in the terminal where `npm run dev` is running

**Option 2: Kill Node processes:**
```powershell
# Find Node processes
Get-Process | Where-Object {$_.ProcessName -like "*node*"}

# Stop all Node processes
Get-Process | Where-Object {$_.ProcessName -like "*node*"} | Stop-Process -Force
```

### Stop Specific Port (If you know the port)

```powershell
# Replace 8000 with your port number
$port = 8000
$connection = netstat -ano | findstr ":$port" | Select-String "LISTENING"
if ($connection) {
    $pid = ($connection -split '\s+')[-1]
    taskkill /PID $pid /F
    Write-Host "Stopped process on port $port (PID: $pid)"
} else {
    Write-Host "Nothing running on port $port"
}
```

### One-Line Command to Stop Everything

**PowerShell:**
```powershell
Get-Process | Where-Object {$_.ProcessName -match "php|node"} | Stop-Process -Force; Write-Host "All PHP and Node processes stopped"
```

**Command Prompt:**
```cmd
taskkill /F /IM php.exe & taskkill /F /IM node.exe
```

---

## After Stopping, Start Fresh

Once you've stopped everything:

**Terminal 1 (Backend):**
```bash
php artisan serve --host=0.0.0.0 --port=9000
```

**Terminal 2 (Frontend):**
```bash
npm run dev
```

Then access: **http://localhost:9000**

