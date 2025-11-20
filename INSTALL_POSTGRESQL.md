# Install PostgreSQL - Step by Step

## Step 1: Download PostgreSQL

1. Go to: https://www.postgresql.org/download/windows/
2. Click "Download the installer"
3. Choose the latest version (e.g., PostgreSQL 16)
4. Download the Windows x86-64 installer

## Step 2: Run Installer

1. Run the downloaded `.exe` file
2. Click "Next" through the setup wizard
3. **Important settings:**
   - **Installation Directory**: Default is fine (`C:\Program Files\PostgreSQL\16`)
   - **Data Directory**: Default is fine (`C:\Program Files\PostgreSQL\16\data`)
   - **Password**: Set a password for the `postgres` superuser (remember this!)
   - **Port**: Default `5432` (keep this)
   - **Advanced Options**: Default locale is fine
   - **Pre Installation Summary**: Review and click "Next"
   - **Ready to Install**: Click "Next"
   - Wait for installation to complete
   - **Completing Setup**: Check "Stack Builder" if you want, then click "Finish"

## Step 3: Verify Installation

Open PowerShell (Run as Administrator) and check service:

```powershell
Get-Service | Where-Object {$_.Name -like "*postgres*"}
```

You should see a service like `postgresql-x64-16` with status `Running`.

## Step 4: Start PostgreSQL Service (if not running)

```powershell
# Find service name
Get-Service | Where-Object {$_.Name -like "*postgres*"}

# Start service (replace with your actual service name)
Start-Service postgresql-x64-16
```

## Step 5: Verify Connection

Open PowerShell and test:

```powershell
# Add PostgreSQL bin to PATH temporarily (if not in PATH)
$env:Path += ";C:\Program Files\PostgreSQL\16\bin"

# Test connection (you'll be prompted for password)
psql -U postgres -h 127.0.0.1 -p 5432
```

Or use pgAdmin:
1. Open pgAdmin (installed with PostgreSQL)
2. Connect to "PostgreSQL 16" server
3. Enter password when prompted

## Step 6: Update .env File

Make sure your `.env` has:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=accounting_central
DB_USERNAME=postgres
DB_PASSWORD=your_password_here
```

Replace `your_password_here` with the password you set during installation.

## Step 7: Run Setup

```bash
php artisan setup:database
```

This will automatically:
- Create `accounting_central` database
- Create `tenant_demo` database
- Run all migrations
- Create demo tenant and admin user

## Step 8: Start Servers

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

## Troubleshooting

**Service won't start?**
- Check if port 5432 is already in use
- Check Windows Event Viewer for errors
- Try restarting the service: `Restart-Service postgresql-x64-16`

**Connection refused?**
- Verify service is running: `Get-Service postgresql-x64-16`
- Check firewall isn't blocking port 5432
- Verify `.env` credentials are correct

**Can't find psql command?**
- Add PostgreSQL bin to PATH:
  - Windows: Settings → System → Advanced → Environment Variables
  - Add: `C:\Program Files\PostgreSQL\16\bin` to PATH
  - Restart PowerShell/terminal

**Forgot password?**
- You can reset it, but it's easier to check pgAdmin for saved credentials
- Or reinstall PostgreSQL (backup data first!)

