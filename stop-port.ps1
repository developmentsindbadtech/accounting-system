# Script to stop processes using specific ports
param(
    [Parameter(Mandatory=$false)]
    [int[]]$Ports = @(8000, 3000, 4000, 5000, 8080, 9000, 7000, 6000),
    
    [Parameter(Mandatory=$false)]
    [switch]$All
)

Write-Host "`n🛑 Stopping processes on ports...`n" -ForegroundColor Yellow

if ($All) {
    Write-Host "Stopping ALL PHP and Node processes...`n" -ForegroundColor Cyan
    
    # Stop all PHP processes
    $phpProcesses = Get-Process | Where-Object {$_.ProcessName -like "*php*"}
    if ($phpProcesses) {
        Write-Host "Found PHP processes:" -ForegroundColor Yellow
        $phpProcesses | ForEach-Object {
            Write-Host "  - PID $($_.Id): $($_.ProcessName)" -ForegroundColor White
            Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue
        }
        Write-Host "✅ Stopped all PHP processes`n" -ForegroundColor Green
    } else {
        Write-Host "✅ No PHP processes found`n" -ForegroundColor Green
    }
    
    # Stop all Node processes
    $nodeProcesses = Get-Process | Where-Object {$_.ProcessName -like "*node*"}
    if ($nodeProcesses) {
        Write-Host "Found Node processes:" -ForegroundColor Yellow
        $nodeProcesses | ForEach-Object {
            Write-Host "  - PID $($_.Id): $($_.ProcessName)" -ForegroundColor White
            Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue
        }
        Write-Host "✅ Stopped all Node processes`n" -ForegroundColor Green
    } else {
        Write-Host "✅ No Node processes found`n" -ForegroundColor Green
    }
    
    exit 0
}

# Stop processes on specific ports
$stopped = $false
foreach ($port in $Ports) {
    Write-Host "Checking port $port..." -ForegroundColor Cyan
    
    # Find processes using this port
    $connections = netstat -ano | findstr ":$port" | findstr "LISTENING"
    
    if ($connections) {
        foreach ($connection in $connections) {
            $parts = $connection -split '\s+'
            $pid = $parts[-1]
            
            if ($pid -match '^\d+$') {
                try {
                    $process = Get-Process -Id $pid -ErrorAction SilentlyContinue
                    if ($process) {
                        Write-Host "  ⚠️  Port $port is used by: $($process.ProcessName) (PID: $pid)" -ForegroundColor Yellow
                        Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
                        Write-Host "  ✅ Stopped process on port $port" -ForegroundColor Green
                        $stopped = $true
                    } else {
                        Write-Host "  ⚠️  Port $port is used by PID: $pid (process not found)" -ForegroundColor Yellow
                    }
                } catch {
                    Write-Host "  ❌ Could not stop process: $_" -ForegroundColor Red
                }
            }
        }
    } else {
        Write-Host "  ✅ Port $port is free" -ForegroundColor Green
    }
}

if (-not $stopped) {
    Write-Host "`n✅ No processes found on the specified ports" -ForegroundColor Green
} else {
    Write-Host "`n✅ Done! Ports should be free now" -ForegroundColor Green
}

Write-Host "`n💡 Tip: Use -All to stop all PHP and Node processes" -ForegroundColor Cyan
Write-Host "   Example: .\stop-port.ps1 -All`n" -ForegroundColor Gray

