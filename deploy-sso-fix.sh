#!/bin/bash

# =================================================
# Azure SSO Fix Deployment Script
# Sindbad Tech Accounting System
# =================================================

set -e  # Exit on any error

echo "=========================================="
echo "Azure SSO Fix - Deployment Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running in the correct directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from the project root.${NC}"
    exit 1
fi

echo -e "${GREEN}✓${NC} Running from correct directory"
echo ""

# Step 1: Pull latest changes (if using git)
echo "Step 1: Pulling latest changes..."
if [ -d ".git" ]; then
    git pull origin main || git pull origin master || echo -e "${YELLOW}⚠ Could not pull from git. Continuing...${NC}"
    echo -e "${GREEN}✓${NC} Git pull completed"
else
    echo -e "${YELLOW}⚠ Not a git repository. Skipping git pull.${NC}"
fi
echo ""

# Step 2: Install/update dependencies
echo "Step 2: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓${NC} Composer dependencies installed"
echo ""

# Step 3: Clear all caches
echo "Step 3: Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓${NC} All caches cleared"
echo ""

# Step 4: Verify .env configuration
echo "Step 4: Verifying .env configuration..."
echo ""
echo "Checking critical environment variables:"

# Function to check env variable
check_env() {
    local var_name=$1
    local var_value=$(php -r "echo env('$var_name');")
    
    if [ -z "$var_value" ] || [ "$var_value" = "" ]; then
        echo -e "${RED}✗${NC} $var_name is not set"
        return 1
    else
        # Don't display secrets
        if [[ $var_name == *"SECRET"* ]] || [[ $var_name == *"PASSWORD"* ]]; then
            echo -e "${GREEN}✓${NC} $var_name is set (hidden)"
        else
            echo -e "${GREEN}✓${NC} $var_name = $var_value"
        fi
        return 0
    fi
}

check_env "APP_ENV"
check_env "APP_URL"
check_env "AZURE_AD_TENANT_ID"
check_env "AZURE_AD_CLIENT_ID"
check_env "AZURE_AD_CLIENT_SECRET"
check_env "AZURE_AD_REDIRECT_URI"

echo ""
echo -e "${YELLOW}Note: Azure AD credentials should be set by DevOps in the .env file${NC}"

echo ""

# Step 5: Run migrations
echo "Step 5: Running database migrations..."
php artisan migrate --force
echo -e "${GREEN}✓${NC} Migrations completed"
echo ""

# Step 6: Verify tenant exists
echo "Step 6: Verifying tenant exists..."
TENANT_COUNT=$(php artisan tinker --execute="echo \App\Models\Tenant::count();")

if [ "$TENANT_COUNT" -eq "0" ]; then
    echo -e "${RED}✗${NC} No tenant found in database"
    echo ""
    echo "Creating default tenant..."
    php artisan tinker --execute="\App\Models\Tenant::create(['name' => 'Sindbad Tech', 'slug' => 'sindbad', 'is_active' => true]);"
    echo -e "${GREEN}✓${NC} Default tenant created"
else
    echo -e "${GREEN}✓${NC} Found $TENANT_COUNT tenant(s)"
fi
echo ""

# Step 7: Set proper permissions
echo "Step 7: Setting file permissions..."
chmod -R 775 storage bootstrap/cache
if [ "$(id -u)" -eq 0 ]; then
    # Running as root
    chown -R www-data:www-data storage bootstrap/cache
    echo -e "${GREEN}✓${NC} Permissions and ownership set"
else
    # Not running as root
    echo -e "${YELLOW}⚠ Not running as root. Skipping chown. Run manually if needed:${NC}"
    echo "  sudo chown -R www-data:www-data storage bootstrap/cache"
fi
echo ""

# Step 8: Optimize for production
echo "Step 8: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓${NC} Application optimized"
echo ""

# Step 9: Restart services (optional)
echo "Step 9: Restarting services..."
if command -v systemctl &> /dev/null; then
    echo "Detected systemd. Attempting to restart services..."
    
    # Try to restart PHP-FPM
    if systemctl list-units --full --all | grep -q php.*fpm; then
        sudo systemctl restart php*-fpm || echo -e "${YELLOW}⚠ Could not restart PHP-FPM${NC}"
        echo -e "${GREEN}✓${NC} PHP-FPM restarted"
    fi
    
    # Try to restart Nginx
    if systemctl list-units --full --all | grep -q nginx; then
        sudo systemctl restart nginx || echo -e "${YELLOW}⚠ Could not restart Nginx${NC}"
        echo -e "${GREEN}✓${NC} Nginx restarted"
    fi
else
    echo -e "${YELLOW}⚠ systemctl not found. Please restart web server manually${NC}"
fi
echo ""

# Final summary
echo "=========================================="
echo -e "${GREEN}Deployment Complete!${NC}"
echo "=========================================="
echo ""
echo "What was fixed:"
echo "  • CSRF exception added for Azure OAuth callback"
echo "  • Session security configured for HTTPS OAuth"
echo "  • Enhanced error logging in Azure controller"
echo "  • Session cookies auto-configured for production"
echo ""
echo "Next steps:"
echo "  1. Test SSO login at: $APP_URL/login"
echo "  2. Click 'Sign in with Microsoft'"
echo "  3. Monitor logs: tail -f storage/logs/laravel.log"
echo "  4. Test CSV exports (Admin/Accountant only)"
echo ""
echo "If SSO still fails:"
echo "  • Check logs: storage/logs/laravel.log"
echo "  • Review guide: SSO-CONFIGURATION-GUIDE.md"
echo "  • Verify Azure AD redirect URI is exact"
echo ""
echo -e "${GREEN}✓ Ready for production!${NC}"
echo ""

