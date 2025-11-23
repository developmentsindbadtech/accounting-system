#!/bin/bash
# DevOps Deployment Script - Run this after pulling code

echo "🚀 Starting deployment..."

# Step 1: Pull latest code (run this manually first, or uncomment below)
# git pull origin main

# Step 2: Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Step 3: Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Step 4: Create user accounts (THE MAIN STEP)
echo "👥 Creating user accounts..."
php artisan users:create-sindbad-tech

# Step 5: Ensure tenant exists
echo "🏢 Ensuring tenant exists..."
php artisan db:seed --class=ProductionTenantSeeder

# Step 6: Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Step 7: Restart services (uncomment if needed)
# echo "🔄 Restarting services..."
# sudo systemctl restart php8.2-fpm
# sudo systemctl restart nginx

echo "✅ Deployment complete!"
echo ""
echo "📋 Accounts created:"
echo "  - revemar.surigao@sindbad.tech (accountant)"
echo "  - hazel.bacalso@sindbad.tech (accountant)"
echo "  - aziz.alsultan@sindbad.tech (accountant)"
echo "  - mohammed.agbawi@sindbad.tech (accountant)"
echo "  - development@sindbad.tech (admin)"
echo ""
echo "🔑 Password for all: Ksa@2021!"
