#!/bin/bash

# Deployment script for production
# This script deploys database changes without migrations

set -e  # Exit on error

echo "=================================="
echo "Production Deployment Script"
echo "=================================="

# Navigate to app directory
cd /var/www/bot_api/app

echo ""
echo "Step 1: Pulling latest code..."
cd /var/www/bot_api
git pull origin main
cd app

echo ""
echo "Step 2: Installing/updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "Step 3: Clearing Symfony cache..."
php bin/console cache:clear --env=prod --no-debug

echo ""
echo "Step 4: Checking database schema changes..."
php bin/console doctrine:schema:update --dump-sql

echo ""
read -p "Do you want to apply these database changes? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    echo "Applying database schema changes..."
    php bin/console doctrine:schema:update --force
    echo "✓ Database schema updated successfully!"
else
    echo "Skipping database schema update."
fi

echo ""
echo "Step 5: Warming up cache..."
php bin/console cache:warmup --env=prod

echo ""
echo "=================================="
echo "✓ Deployment completed successfully!"
echo "=================================="

