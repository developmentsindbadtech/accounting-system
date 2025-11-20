# Check PostgreSQL Status Script

Write-Host "Checking PostgreSQL Status..." -ForegroundColor Cyan
Write-Host ""

# Check for PostgreSQL services
$services = Get-Service | Where-Object {$_.Name -like "*postgres*" -or $_.DisplayName -like "*postgres*"}

if ($services) {
    Write-Host "Found PostgreSQL service(s):" -ForegroundColor Green
    $services | ForEach-Object {
        $statusColor = if ($_.Status -eq 'Running') { 'Green' } else { 'Red' }
        Write-Host "  - $($_.DisplayName)" -ForegroundColor $statusColor
        Write-Host "    Name: $($_.Name)" -ForegroundColor Gray
        Write-Host "    Status: $($_.Status)" -ForegroundColor $statusColor
        Write-Host ""
        
        if ($_.Status -ne 'Running') {
            Write-Host "  To start this service, run as Administrator:" -ForegroundColor Yellow
            Write-Host "    Start-Service $($_.Name)" -ForegroundColor White
            Write-Host ""
        }
    }
} else {
    Write-Host "No PostgreSQL services found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "This could mean:" -ForegroundColor Yellow
    Write-Host "  1. PostgreSQL is not installed" -ForegroundColor White
    Write-Host "  2. Service is named differently" -ForegroundColor White
    Write-Host ""
    Write-Host "Download PostgreSQL:" -ForegroundColor Cyan
    Write-Host "  https://www.postgresql.org/download/windows/" -ForegroundColor White
    Write-Host ""
}

# Check if psql command is available
$psqlPath = Get-Command psql -ErrorAction SilentlyContinue

if ($psqlPath) {
    Write-Host "PostgreSQL client (psql) found: $($psqlPath.Source)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "  1. Start PostgreSQL service if not running (see above)" -ForegroundColor White
    Write-Host "  2. Run: php artisan setup:database" -ForegroundColor White
} else {
    Write-Host "PostgreSQL client (psql) not found in PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Add PostgreSQL bin directory to PATH or use full path to psql.exe" -ForegroundColor Yellow
    Write-Host "Typical location: C:\Program Files\PostgreSQL\XX\bin\psql.exe" -ForegroundColor White
    Write-Host ""
}

Write-Host ""
Write-Host "Troubleshooting:" -ForegroundColor Cyan
Write-Host "  1. Ensure PostgreSQL service is running" -ForegroundColor White
Write-Host "  2. Verify .env has correct credentials" -ForegroundColor White
Write-Host "  3. Check if port 5432 is open" -ForegroundColor White
