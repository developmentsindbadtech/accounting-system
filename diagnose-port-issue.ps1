# Comprehensive diagnostic script for port binding issues
Write-Host "`n🔍 Diagnosing Port Binding Issues...`n" -ForegroundColor Cyan

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

Write-Host "1. Checking Administrator Status..." -ForegroundColor Yellow
if ($isAdmin) {
    Write-Host "   ✅ Running as Administrator" -ForegroundColor Green
} else {
    Write-Host "   ❌ NOT running as Administrator" -ForegroundColor Red
    Write-Host "   ⚠️  This is likely the main issue!" -ForegroundColor Yellow
    Write-Host "   Solution: Right-click PowerShell → Run as Administrator`n" -ForegroundColor Cyan
}

# Check Windows Firewall
Write-Host "`n2. Checking Windows Firewall..." -ForegroundColor Yellow
try {
    $firewallStatus = Get-NetFirewallProfile | Select-Object Name, Enabled
    foreach ($profile in $firewallStatus) {
        if ($profile.Enabled) {
            Write-Host "   ⚠️  Firewall is ON for $($profile.Name) profile" -ForegroundColor Yellow
            Write-Host "      This may be blocking PHP from binding to ports" -ForegroundColor Yellow
        }
    }
} catch {
    Write-Host "   ⚠️  Cannot check firewall (requires admin): $_" -ForegroundColor Yellow
}

# Check port exclusions
Write-Host "`n3. Checking Port Reservations..." -ForegroundColor Yellow
$excluded = netsh interface ipv4 show excludedportrange protocol=tcp
$excludedPorts = $excluded | Select-String "Start Port" -Context 0,1
if ($excludedPorts) {
    Write-Host "   Found reserved ports:" -ForegroundColor Yellow
    $excluded | Select-String "\d+\s+\d+" | ForEach-Object {
        $ports = $_.Line.Trim() -split "\s+"
        Write-Host "      Port $($ports[0]) to $($ports[1])" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ✅ No problematic port reservations found" -ForegroundColor Green
}

# Check for Hyper-V
Write-Host "`n4. Checking for Hyper-V..." -ForegroundColor Yellow
$hyperv = Get-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V-All -ErrorAction SilentlyContinue
if ($hyperv -and $hyperv.State -eq "Enabled") {
    Write-Host "   ⚠️  Hyper-V is enabled" -ForegroundColor Yellow
    Write-Host "      Hyper-V can reserve large port ranges" -ForegroundColor Yellow
    Write-Host "      This might be blocking your ports" -ForegroundColor Yellow
} else {
    Write-Host "   ✅ Hyper-V is not enabled" -ForegroundColor Green
}

# Check what's using common ports
Write-Host "`n5. Checking Port Usage..." -ForegroundColor Yellow
$testPorts = @(3000, 4000, 5000, 8000, 8080)
foreach ($port in $testPorts) {
    $inUse = netstat -ano | findstr ":$port" | findstr "LISTENING"
    if ($inUse) {
        $pid = ($inUse -split '\s+')[-1]
        $process = Get-Process -Id $pid -ErrorAction SilentlyContinue
        if ($process) {
            Write-Host "   ⚠️  Port $port is in use by: $($process.ProcessName) (PID: $pid)" -ForegroundColor Yellow
        } else {
            Write-Host "   ⚠️  Port $port is in use by PID: $pid" -ForegroundColor Yellow
        }
    } else {
        Write-Host "   ✅ Port $port is available" -ForegroundColor Green
    }
}

# Check PHP path and permissions
Write-Host "`n6. Checking PHP Installation..." -ForegroundColor Yellow
$phpPath = (where.exe php 2>$null)
if ($phpPath) {
    $phpPath = $phpPath.Split("`n")[0].Trim()
    Write-Host "   ✅ PHP found at: $phpPath" -ForegroundColor Green
    
    # Check if it's a .bat file (Herd)
    if ($phpPath -like "*.bat") {
        $actualPhp = $phpPath -replace "\.bat$", ".exe"
        if (Test-Path $actualPhp) {
            $phpPath = $actualPhp
        }
    }
    
    # Check file permissions
    $acl = Get-Acl $phpPath -ErrorAction SilentlyContinue
    if ($acl) {
        Write-Host "   ✅ PHP file is accessible" -ForegroundColor Green
    }
} else {
    Write-Host "   ❌ PHP not found in PATH!" -ForegroundColor Red
}

# Summary and recommendations
Write-Host "`n$('='*60)" -ForegroundColor Gray
Write-Host "📋 DIAGNOSIS SUMMARY" -ForegroundColor Cyan
Write-Host "$('='*60)" -ForegroundColor Gray

if (-not $isAdmin) {
    Write-Host "`n❌ PRIMARY ISSUE: Not running as Administrator" -ForegroundColor Red
    Write-Host "`n🔧 SOLUTION:" -ForegroundColor Yellow
    Write-Host "   1. Close this PowerShell window" -ForegroundColor White
    Write-Host "   2. Right-click PowerShell → 'Run as Administrator'" -ForegroundColor White
    Write-Host "   3. Navigate: cd D:\Repository\SindbadTech\Accounting-System\accounting-system" -ForegroundColor White
    Write-Host "   4. Run: php artisan serve --host=127.0.0.1 --port=3000" -ForegroundColor White
} else {
    Write-Host "`n✅ Running as Administrator" -ForegroundColor Green
    Write-Host "`n🔧 ALTERNATIVE SOLUTIONS:" -ForegroundColor Yellow
    Write-Host "   1. Use Laravel Herd (recommended):" -ForegroundColor White
    Write-Host "      - Open Herd Desktop app" -ForegroundColor Cyan
    Write-Host "      - Run: herd link accounting-system" -ForegroundColor Cyan
    Write-Host "      - Access: http://accounting-system.test" -ForegroundColor Cyan
    Write-Host "`n   2. Add PHP to Windows Firewall:" -ForegroundColor White
    Write-Host "      - Run: .\fix-firewall.ps1" -ForegroundColor Cyan
    Write-Host "`n   3. Try different ports:" -ForegroundColor White
    Write-Host "      - php artisan serve --port=7000" -ForegroundColor Cyan
    Write-Host "      - php artisan serve --port=6000" -ForegroundColor Cyan
}

Write-Host "`n$('='*60)" -ForegroundColor Gray

