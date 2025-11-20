@echo off
echo.
echo ========================================
echo   Sindbad.Tech Accounting System Setup
echo ========================================
echo.

REM Check if PostgreSQL psql is available
where psql >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PostgreSQL 'psql' command not found in PATH
    echo.
    echo Please create databases manually:
    echo   1. Open PostgreSQL client (pgAdmin or psql)
    echo   2. Run: CREATE DATABASE accounting_central;
    echo   3. Run: CREATE DATABASE tenant_demo;
    echo.
    echo Then run: php artisan setup:database
    pause
    exit /b 1
)

echo Creating databases...
echo.

REM Create central database
echo Creating accounting_central...
psql -U postgres -c "CREATE DATABASE accounting_central;" 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] accounting_central created
) else (
    echo [INFO] accounting_central may already exist
)

REM Create tenant database
echo Creating tenant_demo...
psql -U postgres -c "CREATE DATABASE tenant_demo;" 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] tenant_demo created
) else (
    echo [INFO] tenant_demo may already exist
)

echo.
echo [OK] Database setup complete!
echo.
echo Next steps:
echo   1. Update .env with your database credentials
echo   2. Run: php artisan setup:database
echo   3. Start servers:
echo      - Terminal 1: php artisan serve
echo      - Terminal 2: npm run dev
echo.
pause

