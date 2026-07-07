#!/usr/bin/env bash
#
# deploy-vps.sh — GLS Sprachenzentrum production deploy (Ubuntu 24.04 VPS)
#
# Run this ON THE SERVER, as a user with sudo access to reload php-fpm/nginx
# and control the gls-worker Supervisor program (either directly, or via a
# deploy user with a narrowly-scoped sudoers entry for those specific
# systemctl/supervisorctl commands — do not run this whole script as root).
#
# Usage:
#   cd /var/www/gls
#   ./scripts/deploy-vps.sh
#
# What it does, in order:
#   1. Puts the app in maintenance mode (brief; skips the request queue window)
#   2. Pulls latest code from main
#   3. Installs PHP + JS dependencies, builds frontend assets
#   4. Runs migrations (never destructive — see MIGRATION_TO_VPS.md)
#   5. Rebuilds all Laravel caches (config/route/view)
#   6. Reloads PHP-FPM (required because OPcache should run with
#      validate_timestamps=0 in production — see PRODUCTION_READINESS.md —
#      so PHP will not notice new code until FPM reloads)
#   7. Reloads Nginx (picks up any config changes; safe no-op otherwise)
#   8. Restarts queue workers (new code must not run inside old worker processes)
#   9. Fixes storage/bootstrap-cache/public-build permissions
#  10. Takes the app back out of maintenance mode
#
# Safe to re-run: every step is idempotent. If a step fails, the script exits
# immediately (set -e) — the app is left in maintenance mode intentionally
# until you've investigated, rather than serving a half-deployed app. Bring it
# back with: php artisan up

set -euo pipefail

APP_DIR="/var/www/gls"
PHP_FPM_SERVICE="php8.4-fpm"
WORKER_PROGRAM="gls-worker:*"
WEB_USER="www-data"

cd "$APP_DIR"

echo "==> [1/10] Entering maintenance mode"
php artisan down --render="errors::503" --retry=15 || true

echo "==> [2/10] Pulling latest code (main)"
git pull origin main

echo "==> [3/10] Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> [3/10] Installing JS dependencies and building assets"
npm install
npm run build

echo "==> [4/10] Running database migrations"
php artisan migrate --force

echo "==> [5/10] Rebuilding caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [6/10] Reloading PHP-FPM (picks up new opcode cache)"
sudo systemctl reload "$PHP_FPM_SERVICE"

echo "==> [7/10] Reloading Nginx"
sudo systemctl reload nginx

echo "==> [8/10] Restarting queue workers"
php artisan queue:restart
sudo supervisorctl restart "$WORKER_PROGRAM"

echo "==> [9/10] Fixing storage / cache / public asset permissions"
sudo chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache public/build
sudo find storage -type d -exec chmod 775 {} \;
sudo find storage -type f -exec chmod 664 {} \;
sudo chmod -R 775 bootstrap/cache

echo "==> [10/10] Leaving maintenance mode"
php artisan up

echo "==> Deploy complete."
echo "    Verify: supervisorctl status ${WORKER_PROGRAM}"
echo "    Verify: tail -f storage/logs/laravel.log"
