# Sindbad.Tech Accounting System - Setup Script
# This script helps you create the PostgreSQL databases

Write-Host "🚀 Sindbad.Tech Accounting System - Database Setup" -ForegroundColor Cyan
Write-Host ""

# Check if PostgreSQL is installed
$psqlPath = Get-Command psql -ErrorAction SilentlyContinue

if (-not $psqlPath) {
    Write-Host "❌ PostgreSQL 'psql' command not found in PATH" -ForegroundColor Red
    Write-Host "Please ensure PostgreSQL is installed and 'psql' is in your PATH" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Alternative: Create databases manually using pgAdmin or any PostgreSQL client:" -ForegroundColor Yellow
    Write-Host "  1. CREATE DATABASE accounting_central;" -ForegroundColor White
    Write-Host "  2. CREATE DATABASE tenant_demo;" -ForegroundColor White
    Write-Host ""
    Write-Host "Then run: php artisan setup:database" -ForegroundColor Yellow
    exit 1
}

Write-Host "📋 This script will create the following databases:" -ForegroundColor Yellow
Write-Host "  - accounting_central (Central database for tenants)" -ForegroundColor White
Write-Host "  - tenant_demo (Demo tenant database)" -ForegroundColor White
Write-Host ""

$dbHost = Read-Host "Enter PostgreSQL host [127.0.0.1]"
if ([string]::IsNullOrWhiteSpace($dbHost)) { $dbHost = "127.0.0.1" }

$dbPort = Read-Host "Enter PostgreSQL port [5432]"
if ([string]::IsNullOrWhiteSpace($dbPort)) { $dbPort = "5432" }

$dbUser = Read-Host "Enter PostgreSQL username [postgres]"
if ([string]::IsNullOrWhiteSpace($dbUser)) { $dbUser = "postgres" }

$dbPassword = Read-Host "Enter PostgreSQL password" -AsSecureString
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassword)
$dbPasswordPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

Write-Host ""
Write-Host "Creating databases..." -ForegroundColor Yellow

# Create central database
Write-Host "Creating accounting_central..." -ForegroundColor Cyan
$env:PGPASSWORD = $dbPasswordPlain
$createCentral = "CREATE DATABASE accounting_central;" | psql -h $dbHost -p $dbPort -U $dbUser -d postgres 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ accounting_central created" -ForegroundColor Green
} else {
    if ($createCentral -match "already exists") {
        Write-Host "ℹ️  accounting_central already exists" -ForegroundColor Yellow
    } else {
        Write-Host "❌ Error: $createCentral" -ForegroundColor Red
    }
}

# Create tenant database
Write-Host "Creating tenant_demo..." -ForegroundColor Cyan
$createTenant = "CREATE DATABASE tenant_demo;" | psql -h $dbHost -p $dbPort -U $dbUser -d postgres 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ tenant_demo created" -ForegroundColor Green
} else {
    if ($createTenant -match "already exists") {
        Write-Host "ℹ️  tenant_demo already exists" -ForegroundColor Yellow
    } else {
        Write-Host "❌ Error: $createTenant" -ForegroundColor Red
    }
}

$env:PGPASSWORD = $null

Write-Host ""
Write-Host "✅ Database creation complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Cyan
Write-Host "  1. Make sure your .env file has correct database credentials" -ForegroundColor White
Write-Host "  2. Run: php artisan setup:database" -ForegroundColor White
Write-Host "  3. Start servers:" -ForegroundColor White
Write-Host "     - Terminal 1: php artisan serve" -ForegroundColor White
Write-Host "     - Terminal 2: npm run dev" -ForegroundColor White

