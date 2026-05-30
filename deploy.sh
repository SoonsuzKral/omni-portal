#!/bin/bash
# Omni Portal - Deployment Script
# Run on Hostinger after git push

set -e

PROJECT_DIR="/domains/omviportal.com/laravel"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deployment started..." >> "$LOG_FILE"

cd "$PROJECT_DIR"

# Pull latest code
git pull origin main 2>&1 >> "$LOG_FILE"

# Install PHP dependencies (no dev, optimized)
/usr/local/bin/php81 composer install --no-dev --optimize-autoloader 2>&1 >> "$LOG_FILE"

# Cache Laravel
/usr/local/bin/php81 artisan config:cache 2>&1 >> "$LOG_FILE"
/usr/local/bin/php81 artisan route:cache 2>&1 >> "$LOG_FILE"
/usr/local/bin/php81 artisan view:cache 2>&1 >> "$LOG_FILE"

# Run migrations (force = no prompt)
/usr/local/bin/php81 artisan migrate --force 2>&1 >> "$LOG_FILE"

# Rebuild sitemap cache
/usr/local/bin/php81 artisan sitemap:rebuild --now 2>&1 >> "$LOG_FILE"

# Ping Google & Bing about sitemap update
/usr/local/bin/php81 artisan sitemap:ping 2>&1 >> "$LOG_FILE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deployment completed." >> "$LOG_FILE"
