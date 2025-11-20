# 🚀 STAS Production Deployment Guide

## Subdomain: `stas.sindbad.tech`
## Server: Google Cloud Platform (GCP)

---

## 📋 Pre-Deployment Checklist

### 1. **Azure AD Configuration**
- [ ] Register app in Azure Portal: `https://portal.azure.com`
- [ ] Set Redirect URI: `https://stas.sindbad.tech/login/azure/callback`
- [ ] Copy Client ID and Client Secret
- [ ] Configure Tenant ID (if using single tenant)

### 2. **Google Cloud Platform Setup**
- [ ] Create GCP project
- [ ] Set up Compute Engine VM or Cloud Run
- [ ] Configure firewall rules (ports 80, 443)
- [ ] Set up Cloud SQL (PostgreSQL recommended) or use VM database
- [ ] Configure domain DNS (A record or CNAME)

### 3. **Domain Configuration (Squarespace)**
- [ ] Add subdomain `stas` pointing to GCP server IP
- [ ] Configure SSL certificate (Let's Encrypt recommended)

---

## 🔧 Server Requirements

### Minimum Specifications:
- **PHP**: 8.2 or higher
- **Database**: PostgreSQL 13+ or MySQL 8.0+
- **Web Server**: Nginx or Apache
- **Extensions**: 
  - PDO
  - OpenSSL
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - GD or Imagick

### Recommended GCP Setup:
- **VM Instance**: e2-standard-2 (2 vCPU, 8GB RAM)
- **OS**: Ubuntu 22.04 LTS
- **Database**: Cloud SQL PostgreSQL (db-f1-micro minimum)

---

## 📦 Step-by-Step Deployment

### Step 1: Prepare Repository

```bash
# Clone repository
git clone <your-repo-url>
cd accounting-system

# Create .env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 2: Configure Environment Variables

Edit `.env` file with production values:

```env
APP_NAME="Sindbad.Tech Accounting System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://stas.sindbad.tech

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=YOUR_DB_HOST
DB_PORT=5432
DB_DATABASE=stas_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Azure AD SSO Configuration
AZURE_CLIENT_ID=your_azure_client_id
AZURE_CLIENT_SECRET=your_azure_client_secret
AZURE_REDIRECT_URI=https://stas.sindbad.tech/login/azure/callback
AZURE_TENANT_ID=your_tenant_id

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration (if using)
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@stas.sindbad.tech
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 3: Install Dependencies

```bash
# Install Composer dependencies (production only)
composer install --no-dev --optimize-autoloader

# Install Node dependencies (if using Vite)
npm install
npm run build
```

### Step 4: Database Setup

```bash
# Run migrations
php artisan migrate --force

# (Optional) Seed initial data
php artisan db:seed --class=FinTechCompanySampleDataSeeder
```

### Step 5: Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### Step 6: Set Permissions

```bash
# Set storage permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create storage link
php artisan storage:link
```

### Step 7: Configure Web Server

#### Nginx Configuration (`/etc/nginx/sites-available/stas.sindbad.tech`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name stas.sindbad.tech;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name stas.sindbad.tech;
    
    root /var/www/stas/public;
    index index.php;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/stas.sindbad.tech/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/stas.sindbad.tech/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/stas.sindbad.tech /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 8: SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d stas.sindbad.tech

# Auto-renewal (already configured by certbot)
sudo certbot renew --dry-run
```

### Step 9: PHP-FPM Configuration

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

### Step 10: Setup Supervisor (for queues, if needed)

Create `/etc/supervisor/conf.d/stas-worker.conf`:

```ini
[program:stas-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/stas/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/stas/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start stas-worker:*
```

---

## 🔄 Deployment Script

Use the provided `deploy.sh` script for automated deployment:

```bash
chmod +x deploy.sh
./deploy.sh
```

Or manually run optimization commands:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan migrate --force
```

---

## 🔍 Post-Deployment Verification

### 1. Test Application
- [ ] Visit `https://stas.sindbad.tech`
- [ ] Test Microsoft SSO login
- [ ] Verify role-based access (Admin, Accountant, Viewer)
- [ ] Test creating records (Journal Entries, Invoices, etc.)
- [ ] Verify reports are working (Trial Balance, P&L, Balance Sheet)

### 2. Check Logs
```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### 3. Performance Check
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Nginx status
sudo systemctl status nginx

# Check database connections
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"
```

---

## 🛠️ Maintenance Commands

### Clear Caches (if needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Re-optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Backup Database
```bash
# PostgreSQL
pg_dump -U username -d stas_production > backup_$(date +%Y%m%d).sql

# MySQL
mysqldump -u username -p stas_production > backup_$(date +%Y%m%d).sql
```

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` in `.env`
- [ ] Strong database passwords
- [ ] SSL certificate installed and auto-renewing
- [ ] Firewall configured (only ports 80, 443 open)
- [ ] Regular security updates: `sudo apt update && sudo apt upgrade`
- [ ] Database backups scheduled
- [ ] Application logs monitored
- [ ] Rate limiting configured (if needed)

---

## 📊 Monitoring & Performance

### Recommended Tools:
- **Google Cloud Monitoring**: Monitor VM resources
- **Laravel Telescope** (dev only): Application debugging
- **Sentry**: Error tracking (optional)
- **New Relic** or **Datadog**: APM (optional)

### Performance Optimization:
1. Enable OPcache in PHP
2. Use Redis for caching
3. Configure database connection pooling
4. Enable HTTP/2 in Nginx
5. Use CDN for static assets (optional)

---

## 🆘 Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log

# Check Nginx error logs
tail -f /var/log/nginx/error.log
```

### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

### Database Connection Issues
```bash
# Test PostgreSQL connection
psql -h YOUR_DB_HOST -U YOUR_DB_USER -d stas_production

# Check firewall rules
sudo ufw status
```

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review this deployment guide
- Contact DevOps team

---

**Last Updated**: November 20, 2025
**Version**: 1.0.0

