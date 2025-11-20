# Comprehensive script to start Laravel server on localhost
Write-Host "`n🔍 Diagnosing localhost server issues...`n" -ForegroundColor Cyan

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: Not in Laravel project directory!" -ForegroundColor Red
    Write-Host "Please run: cd accounting-system" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Found Laravel project" -ForegroundColor Green

# Check PHP
Write-Host "`n📋 Checking PHP..." -ForegroundColor Cyan
$phpVersion = php -v 2>&1 | Select-String "PHP"
if ($phpVersion) {
    Write-Host "   ✅ PHP is installed: $($phpVersion.Line.Split(' ')[1])" -ForegroundColor Green
} else {
    Write-Host "   ❌ PHP not found!" -ForegroundColor Red
    exit 1
}

# Check Laravel
Write-Host "`n📋 Checking Laravel..." -ForegroundColor Cyan
$laravelVersion = php artisan --version 2>&1
if ($laravelVersion -like "*Laravel*") {
    Write-Host "   ✅ Laravel is working: $laravelVersion" -ForegroundColor Green
} else {
    Write-Host "   ❌ Laravel not working!" -ForegroundColor Red
    exit 1
}

# Check if .env exists
Write-Host "`n📋 Checking configuration..." -ForegroundColor Cyan
if (Test-Path ".env") {
    Write-Host "   ✅ .env file exists" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  .env file not found - copying from .env.example" -ForegroundColor Yellow
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "   ✅ Created .env file - please configure it!" -ForegroundColor Yellow
        Write-Host "   Run: php artisan key:generate" -ForegroundColor Cyan
    }
}

# Check for port conflicts
Write-Host "`n📋 Checking available ports..." -ForegroundColor Cyan
$ports = @(8000, 3000, 8080, 9000, 5000, 4000)
$availablePort = $null

foreach ($port in $ports) {
    $inUse = netstat -ano | findstr ":$port" | findstr "LISTENING"
    if (-not $inUse) {
        $availablePort = $port
        Write-Host "   ✅ Port $port is available" -ForegroundColor Green
        break
    } else {
        Write-Host "   ⚠️  Port $port is in use" -ForegroundColor Yellow
    }
}

if (-not $availablePort) {
    Write-Host "   ❌ No available ports found!" -ForegroundColor Red
    Write-Host "   Try stopping other services or restart your computer" -ForegroundColor Yellow
    exit 1
}

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "`n⚠️  Not running as Administrator" -ForegroundColor Yellow
    Write-Host "   Some port issues may require admin privileges" -ForegroundColor Yellow
    Write-Host "   If this fails, try: Right-click PowerShell → Run as Administrator`n" -ForegroundColor Yellow
}

# Try to start server
Write-Host "`n🚀 Starting Laravel server on port $availablePort...`n" -ForegroundColor Green
Write-Host "   Access your app at: http://localhost:$availablePort" -ForegroundColor Cyan
Write-Host "   Press Ctrl+C to stop the server`n" -ForegroundColor Yellow
Write-Host "─" * 60 -ForegroundColor Gray
Write-Host ""

# Try different methods
$methods = @(
    @{name="Standard method"; cmd="php artisan serve --host=127.0.0.1 --port=$availablePort"},
    @{name="Alternative host"; cmd="php artisan serve --host=0.0.0.0 --port=$availablePort"},
    @{name="PHP built-in server"; cmd="php -S 127.0.0.1:$availablePort -t public"}
)

foreach ($method in $methods) {
    Write-Host "Trying: $($method.name)..." -ForegroundColor Cyan
    try {
        Invoke-Expression $method.cmd
        # If we get here, server started successfully
        break
    } catch {
        Write-Host "   Failed: $_" -ForegroundColor Red
        Write-Host "   Trying next method...`n" -ForegroundColor Yellow
        Start-Sleep -Seconds 2
    }
}

Write-Host "`n✅ Server should be running now!" -ForegroundColor Green
Write-Host "   If you see errors above, try running PowerShell as Administrator" -ForegroundColor Yellow

