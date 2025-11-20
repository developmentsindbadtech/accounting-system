# Script to start Laravel server on an available port
Write-Host "Trying to start Laravel server..." -ForegroundColor Yellow

$ports = @(3000, 4000, 8080, 8888, 9999, 7000, 6000)

foreach ($port in $ports) {
    Write-Host "`nTrying port $port..." -ForegroundColor Cyan
    
    # Check if port is available
    $connection = netstat -ano | findstr ":$port" | findstr "LISTENING"
    if ($connection) {
        Write-Host "Port $port is already in use, trying next..." -ForegroundColor Yellow
        continue
    }
    
    Write-Host "Starting server on port $port..." -ForegroundColor Green
    Write-Host "Access your app at: http://localhost:$port" -ForegroundColor Green
    Write-Host "`nPress Ctrl+C to stop the server`n" -ForegroundColor Yellow
    
    # Try to start the server
    php artisan serve --host=127.0.0.1 --port=$port
    
    # If we get here, the server started successfully
    break
}

Write-Host "`nIf all ports failed, try running PowerShell as Administrator" -ForegroundColor Red


