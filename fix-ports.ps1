# Script to check and potentially fix port reservation issues
Write-Host "Checking for Hyper-V port reservations..." -ForegroundColor Yellow

# Check if Hyper-V is reserving ports
$hyperv = Get-NetReservedPortRange | Where-Object { $_.StartPort -le 9000 -and $_.EndPort -ge 8000 }
if ($hyperv) {
    Write-Host "Found reserved port ranges:" -ForegroundColor Red
    $hyperv | Format-Table
    
    Write-Host "`nTo remove Hyper-V port reservations (requires Admin):" -ForegroundColor Yellow
    Write-Host "netsh int ipv4 delete excludedportrange protocol=tcp startport=XXXX numberofports=XXXX" -ForegroundColor Cyan
    Write-Host "`nOr disable Hyper-V if not needed:" -ForegroundColor Yellow
    Write-Host "Disable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V-All" -ForegroundColor Cyan
} else {
    Write-Host "No conflicting port reservations found in 8000-9000 range" -ForegroundColor Green
}

Write-Host "`nTrying alternative ports..." -ForegroundColor Yellow
Write-Host "Try these ports that are less likely to be blocked:" -ForegroundColor Cyan
Write-Host "  - 3000" -ForegroundColor White
Write-Host "  - 4000" -ForegroundColor White
Write-Host "  - 8080" -ForegroundColor White
Write-Host "  - 8888" -ForegroundColor White
Write-Host "  - 9999" -ForegroundColor White


