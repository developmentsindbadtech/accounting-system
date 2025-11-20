#!/bin/bash

# Production Deployment Script for STAS (Sindbad.Tech Accounting System)
# Subdomain: stas.sindbad.tech
# Server: Google Cloud Platform

set -e

echo "🚀 Starting STAS Production Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    echo "Please create .env file from .env.example"
    exit 1
fi

# Set production environment
export APP_ENV=production
export APP_DEBUG=false

echo -e "${YELLOW}📦 Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}🔧 Optimizing Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${YELLOW}🗄️  Running migrations...${NC}"
php artisan migrate --force

echo -e "${YELLOW}🔐 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo -e "${YELLOW}🧹 Clearing old caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo -e "${YELLOW}⚡ Re-optimizing...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}🌐 Application is ready at: https://stas.sindbad.tech${NC}"

