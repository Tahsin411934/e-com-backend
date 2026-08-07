#!/bin/bash

# ============================================
# EC2 Deployment Script for Laravel E-Commerce
# ============================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  E-Commerce Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"

# Project directory
PROJECT_DIR="/var/www/e-com-backend"
cd "$PROJECT_DIR"

# 1. Set proper permissions
echo -e "${YELLOW}[1/8] Setting permissions...${NC}"
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 2. Install Composer dependencies
echo -e "${YELLOW}[2/8] Installing Composer dependencies...${NC}"
composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# 3. Setup .env file if it doesn't exist
echo -e "${YELLOW}[3/8] Checking .env file...${NC}"
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.example...${NC}"
    sudo cp .env.example .env
    sudo php artisan key:generate
    echo -e "${GREEN}.env file created and key generated${NC}"
else
    echo -e "${GREEN}.env file already exists${NC}"
fi

# 4. Run database migrations
echo -e "${YELLOW}[4/8] Running database migrations...${NC}"
php artisan migrate --force

# 5. Clear and cache configuration
echo -e "${YELLOW}[5/8] Clearing and caching configuration...${NC}"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Create storage link
echo -e "${YELLOW}[6/8] Creating storage link...${NC}"
php artisan storage:link || true

# 7. Set final permissions
echo -e "${YELLOW}[7/8] Setting final permissions...${NC}"
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Restart PHP-FPM
echo -e "${YELLOW}[8/8] Restarting PHP-FPM...${NC}"
sudo systemctl restart php8.5-fpm || sudo systemctl restart php-fpm || true

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"