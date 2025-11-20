# 🚀 DevOps Quick Reference - STAS

## Target: `stas.sindbad.tech` on Google Cloud Platform

---

## 📋 Pre-Deployment (Do Once)

### 1. Azure AD Setup
```
Portal: https://portal.azure.com
Path: Azure AD → App registrations → New registration
Name: STAS Production
Redirect URI: https://stas.sindbad.tech/login/azure/callback
Copy: Client ID, Client Secret, Tenant ID
```

### 2. Squarespace DNS
```
Subdomain: stas
Type: A record (or CNAME)
Value: GCP server IP address
TTL: 3600
```

### 3. GCP VM Creation
```bash
gcloud compute instances create stas-server \
  --zone=us-central1-a \
  --machine-type=e2-standard-2 \
  --image-family=ubuntu-2204-lts \
  --image-project=ubuntu-os-cloud \
  --boot-disk-size=20GB
```

---

## 🔧 Server Setup (One-Time)

```bash
# SSH into server
gcloud compute ssh stas-server --zone=us-central1-a

# Install PHP 8.2
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
  php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
  php8.2-bcmath php8.2-pgsql

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PostgreSQL (or use Cloud SQL)
sudo apt install -y postgresql postgresql-contrib
```

---

## 📦 Application Deployment

```bash
# 1. Clone repository
cd /var/www
sudo git clone <your-repo-url> stas
cd stas/accounting-system

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
sudo cp .env.example .env
sudo nano .env
# Add all production values (see .env template below)

# 4. Generate key and setup
php artisan key:generate
php artisan migrate --force

# 5. Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
php artisan storage:link

# 6. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ⚙️ Nginx Configuration

**File**: `/etc/nginx/sites-available/stas.sindbad.tech`

```nginx
server {
    listen 80;
    server_name stas.sindbad.tech;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name stas.sindbad.tech;
    
    root /var/www/stas/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/stas.sindbad.tech/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/stas.sindbad.tech/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable**:
```bash
sudo ln -s /etc/nginx/sites-available/stas.sindbad.tech /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔐 SSL Certificate

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d stas.sindbad.tech
```

---

## 📝 Production .env Template

```env
APP_NAME="Sindbad.Tech Accounting System"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
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
QUEUE_CONNECTION=database
```

---

## 🔄 Update Deployment (After Git Push)

```bash
cd /var/www/stas/accounting-system
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo systemctl reload php8.2-fpm
```

---

## 🆘 Quick Troubleshooting

**500 Error?**
```bash
tail -f storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

**Clear Everything?**
```bash
php artisan optimize:clear
php artisan optimize
```

**Permission Issues?**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

---

## ✅ Post-Deployment Verification

1. Visit: `https://stas.sindbad.tech`
2. Test Microsoft SSO login
3. Verify role-based access
4. Test create/edit/delete operations
5. Check reports (Trial Balance, P&L, Balance Sheet)

---

**For detailed instructions, see `DEPLOYMENT.md`**

