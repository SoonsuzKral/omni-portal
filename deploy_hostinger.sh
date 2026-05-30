#!/bin/bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="$PROJECT_ROOT/build-deploy"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo "============================================"
echo " Omni Portal - Hostinger Deployment Script"
echo " Timestamp: $TIMESTAMP"
echo "============================================"
echo ""

# Step 1: Install Composer (no-dev, optimized)
echo "[1/7] Composer install --no-dev..."
cd "$PROJECT_ROOT"
composer install --no-dev --optimize-autoloader --no-interaction

# Step 2: Build frontend
echo "[2/7] Building frontend assets..."
npm run build

# Step 3: Cache Laravel
echo "[3/7] Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 4: Migrate
echo "[4/7] Running migrations..."
php artisan migrate --force

# Step 5: Prepare staging directories
echo "[5/7] Assembling deployment tree..."
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/laravel" "$BUILD_DIR/public_html"

# Copy all Laravel files EXCEPT public/ and junk
rsync -a --delete \
  --exclude='public' \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='.claude' \
  --exclude='build-deploy' \
  --exclude='deploy_hostinger.sh' \
  --exclude='__pycache__' \
  --exclude='*.py' \
  --exclude='*.log' \
  --exclude='*.checkpoint.json' \
  --exclude='niche_matrix_*.json' \
  --exclude='settings.local.json' \
  --exclude='TRENDSENSE_*.md' \
  "$PROJECT_ROOT/" "$BUILD_DIR/laravel/"

# Copy public/ contents into public_html/
cp -r "$PROJECT_ROOT/public/"* "$BUILD_DIR/public_html/"

# Step 6: Patch index.php for Hostinger paths
echo "[6/7] Patching public_html/index.php..."
cat > "$BUILD_DIR/public_html/index.php" << 'PHPEOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laravel/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHPEOF

# Step 7: Create ZIPs
echo "[7/7] Creating deployment archives..."
cd "$BUILD_DIR"

zip -r "$PROJECT_ROOT/laravel_app.zip" laravel/
zip -r "$PROJECT_ROOT/public_html_files.zip" public_html/

cd "$PROJECT_ROOT"
rm -rf "$BUILD_DIR"

echo ""
echo "============================================"
echo " SUCCESS - Deployment archives created:"
echo ""
echo "  1. laravel_app.zip"
echo "     -> Extract to: /home/u389331892/"
echo "     -> Creates:    /home/u389331892/laravel/"
echo ""
echo "  2. public_html_files.zip"
echo "     -> Extract to: /home/u389331892/public_html/"
echo "     -> Overwrites: index.php, .htaccess, assets"
echo ""
echo " Post-upload checklist:"
echo "  - Create /home/u389331892/laravel/.env with DB creds"
echo "  - chmod 755 /home/u389331892/laravel/storage"
echo "  - chmod 755 /home/u389331892/laravel/bootstrap/cache"
echo "  - Upload google-service-account.json to storage/"
echo "============================================"
