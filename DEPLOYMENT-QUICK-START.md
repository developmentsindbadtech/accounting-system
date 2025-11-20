# 🚀 Quick Deployment Guide - STAS

## Subdomain: `stas.sindbad.tech` | Server: Google Cloud Platform

---

## ⚡ Quick Start (5 Steps)

### 1. **GCP Server Setup**
```bash
# Create VM instance
gcloud compute instances create stas-server \
  --zone=us-central1-a \
  --machine-type=e2-standard-2 \
  --image-family=ubuntu-2204-lts \
  --image-project=ubuntu-os-cloud \
  --boot-disk-size=20GB

# SSH into server
gcloud compute ssh stas-server --zone=us-central1-a
```

### 2. **Install Dependencies**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip \
  php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-pgsql

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PostgreSQL (or use Cloud SQL)
sudo apt install -y postgresql postgresql-contrib
```

### 3. **Deploy Application**
```bash
# Clone repository
cd /var/www
sudo git clone <your-repo-url> stas
cd stas/accounting-system

# Install dependencies
composer install --no-dev --optimize-autoloader

# Configure environment
cp .env.example .env
nano .env  # Edit with production values

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force
```

### 4. **Configure Nginx**
```bash
# Copy nginx config from DEPLOYMENT.md
sudo nano /etc/nginx/sites-available/stas.sindbad.tech

# Enable site
sudo ln -s /etc/nginx/sites-available/stas.sindbad.tech /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. **SSL Certificate**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d stas.sindbad.tech
```

---

## 🔧 Environment Variables (.env)

```env
APP_NAME="Sindbad.Tech Accounting System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://stas.sindbad.tech

DB_CONNECTION=pgsql
DB_HOST=YOUR_DB_HOST
DB_PORT=5432
DB_DATABASE=stas_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

AZURE_AD_CLIENT_ID=your_azure_client_id
AZURE_AD_CLIENT_SECRET=your_azure_client_secret
AZURE_AD_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback
AZURE_AD_TENANT_ID=your_tenant_id

SESSION_DRIVER=database
CACHE_DRIVER=redis
```

---

## 📝 Azure AD Configuration

1. Go to: `https://portal.azure.com`
2. Navigate to: **Azure Active Directory** → **App registrations**
3. Create new registration:
   - Name: `STAS Production`
   - Redirect URI: `https://stas.sindbad.tech/login/azure/callback`
4. Copy **Application (client) ID** → `AZURE_AD_CLIENT_ID`
5. Create **Client secret** → `AZURE_AD_CLIENT_SECRET`
6. Copy **Directory (tenant) ID** → `AZURE_AD_TENANT_ID`

---

## 🌐 Domain Configuration (Squarespace)

1. Log in to Squarespace
2. Go to **Settings** → **Domains**
3. Add subdomain: `stas`
4. Point to GCP server IP (A record) or use CNAME
5. Wait for DNS propagation (5-30 minutes)

---

## ⚙️ Post-Deployment Optimization

```bash
# Run optimization script
chmod +x optimize-production.sh
./optimize-production.sh

# Or manually:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ✅ Verification Checklist

- [ ] Application loads at `https://stas.sindbad.tech`
- [ ] Microsoft SSO login works
- [ ] User roles are assigned correctly
- [ ] Can create/edit/delete records (as Admin/Accountant)
- [ ] Reports are working (Trial Balance, P&L, Balance Sheet)
- [ ] SSL certificate is valid
- [ ] No errors in logs: `tail -f storage/logs/laravel.log`

---

## 🆘 Quick Troubleshooting

**500 Error?**
```bash
tail -f storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

**Permission Issues?**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

**Clear Caches?**
```bash
php artisan optimize:clear
php artisan optimize
```

---

**For detailed instructions, see `DEPLOYMENT.md`**

