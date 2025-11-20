# Script to add PHP to Windows Firewall (Run as Administrator)
Write-Host "Adding PHP to Windows Firewall..." -ForegroundColor Yellow

# Find PHP executable
$phpPath = (where.exe php).Split("`n")[0].Trim()
if (-not $phpPath) {
    Write-Host "PHP not found in PATH!" -ForegroundColor Red
    exit 1
}

# Resolve to actual .exe if it's a .bat file
if ($phpPath -like "*.bat") {
    $phpPath = $phpPath -replace "\.bat$", ".exe"
    # Try to find actual PHP exe
    $herdPhpPath = "C:\Users\$env:USERNAME\.config\herd\bin\php.exe"
    if (Test-Path $herdPhpPath) {
        $phpPath = $herdPhpPath
    }
}

Write-Host "Found PHP at: $phpPath" -ForegroundColor Green

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "`nERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Write-Host "Then run this script again." -ForegroundColor Yellow
    exit 1
}

# Add firewall rule for PHP
Write-Host "`nAdding firewall rule for PHP..." -ForegroundColor Cyan

try {
    # Remove existing rule if it exists
    netsh advfirewall firewall delete rule name="PHP Laravel Server" 2>$null
    
    # Add new rule
    netsh advfirewall firewall add rule name="PHP Laravel Server" dir=in action=allow program="$phpPath" enable=yes
    
    Write-Host "`n✅ Successfully added PHP to Windows Firewall!" -ForegroundColor Green
    Write-Host "`nNow try starting your server:" -ForegroundColor Yellow
    Write-Host "  php artisan serve --host=127.0.0.1 --port=3000" -ForegroundColor Cyan
} catch {
    Write-Host "`n❌ Failed to add firewall rule: $_" -ForegroundColor Red
    Write-Host "`nTry manually:" -ForegroundColor Yellow
    Write-Host "1. Open Windows Security" -ForegroundColor White
    Write-Host "2. Firewall & network protection" -ForegroundColor White
    Write-Host "3. Allow an app through firewall" -ForegroundColor White
    Write-Host "4. Add: $phpPath" -ForegroundColor White
}


