# Script to restart services that might be blocking ports
Write-Host "Restarting network and related services..." -ForegroundColor Yellow

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "`n⚠️  Some operations require Administrator privileges" -ForegroundColor Yellow
    Write-Host "Right-click PowerShell and select 'Run as Administrator' for full functionality`n" -ForegroundColor Yellow
}

# 1. Stop any PHP processes
Write-Host "`n1. Stopping PHP processes..." -ForegroundColor Cyan
Get-Process | Where-Object {$_.ProcessName -like "*php*"} | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1
Write-Host "   ✅ PHP processes stopped" -ForegroundColor Green

# 2. Stop any Node processes (Vite)
Write-Host "`n2. Stopping Node processes..." -ForegroundColor Cyan
Get-Process | Where-Object {$_.ProcessName -like "*node*"} | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1
Write-Host "   ✅ Node processes stopped" -ForegroundColor Green

# 3. Restart network services (requires admin)
if ($isAdmin) {
    Write-Host "`n3. Restarting network services..." -ForegroundColor Cyan
    try {
        Restart-Service -Name "Dnscache" -ErrorAction SilentlyContinue
        Restart-Service -Name "NlaSvc" -ErrorAction SilentlyContinue
        Write-Host "   ✅ Network services restarted" -ForegroundColor Green
    } catch {
        Write-Host "   ⚠️  Could not restart network services: $_" -ForegroundColor Yellow
    }
} else {
    Write-Host "`n3. Skipping network services (requires admin)" -ForegroundColor Yellow
}

# 4. Restart Windows Firewall service (requires admin)
if ($isAdmin) {
    Write-Host "`n4. Restarting Windows Firewall service..." -ForegroundColor Cyan
    try {
        Restart-Service -Name "MpsSvc" -ErrorAction SilentlyContinue
        Write-Host "   ✅ Firewall service restarted" -ForegroundColor Green
    } catch {
        Write-Host "   ⚠️  Could not restart firewall: $_" -ForegroundColor Yellow
    }
} else {
    Write-Host "`n4. Skipping firewall restart (requires admin)" -ForegroundColor Yellow
}

# 5. Clear port reservations (if Hyper-V is the issue)
if ($isAdmin) {
    Write-Host "`n5. Checking for port reservations..." -ForegroundColor Cyan
    $reserved = netsh interface ipv4 show excludedportrange protocol=tcp | Select-String "Start Port"
    if ($reserved) {
        Write-Host "   Found reserved ports. Check with:" -ForegroundColor Yellow
        Write-Host "   netsh interface ipv4 show excludedportrange protocol=tcp" -ForegroundColor Cyan
    } else {
        Write-Host "   ✅ No problematic port reservations found" -ForegroundColor Green
    }
}

Write-Host "`n✅ Service restart complete!" -ForegroundColor Green
Write-Host "`nNow try starting your server:" -ForegroundColor Yellow
Write-Host "  php artisan serve --host=127.0.0.1 --port=3000" -ForegroundColor Cyan
Write-Host "`nOr use Laravel Herd (recommended):" -ForegroundColor Yellow
Write-Host "  herd link accounting-system" -ForegroundColor Cyan


