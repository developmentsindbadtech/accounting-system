#!/bin/bash

# Production Optimization Script for STAS
# Run this after deployment to optimize performance

set -e

echo "⚡ Optimizing STAS for Production..."

# Clear all caches first
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative

# Clear application cache
php artisan optimize:clear
php artisan optimize

echo "✅ Optimization complete!"

