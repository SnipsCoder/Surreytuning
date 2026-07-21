#!/usr/bin/env bash
# Paste this into the Hostinger web terminal, run as the app user, from the app
# directory: /home/user/htdocs/srv1799606.hstgr.cloud
#
# Follows docs/deploy-runbook.md exactly - do not reorder the cache-rebuild step
# (config:cache must run LAST) or swap migrate:fresh/migrate:refresh in for the
# forward-only migrate commands.
#
# No .env changes ship with this release, so the secret-paste-masking trap
# (memory/surreytuning-production-server.md) does not apply here - nothing in
# this script writes to .env. Skip straight to verification once it finishes.

set -euo pipefail

APP_DIR="/home/user/htdocs/srv1799606.hstgr.cloud"
cd "$APP_DIR"

echo "== 0. Pull the new release =="
sudo -u user git pull --ff-only
sudo -u user composer install --no-dev --optimize-autoloader
sudo -u user npm ci
sudo -u user npm run build

echo "== 1. Maintenance mode =="
sudo -u user php artisan down --render="errors::503" --retry=15

echo "== 2. Central database migrations =="
sudo -u user php artisan migrate --force

echo "== 3. Tenant database migrations =="
sudo -u user php artisan tenants:migrate --force

echo "== 4. Rebuild caches (config LAST) =="
sudo -u user php artisan route:cache
sudo -u user php artisan event:cache
sudo -u user php artisan view:cache
sudo -u user php artisan config:cache

echo "== 5. Restart queue workers =="
sudo -u user php artisan queue:restart

echo "== 6. Bring the app back up =="
sudo -u user php artisan up

echo "== Done. Now verify: =="
echo "curl -fsS https://surreytuning.co.uk/healthz"
echo "sudo -u user php artisan queue:failed"
