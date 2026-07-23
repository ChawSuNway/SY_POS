#!/bin/sh
set -e

# Render injects PORT env var - bind Apache to it
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Fall back to Render external URL when APP_URL is not set
if [ -z "${APP_URL}" ] && [ -n "${RENDER_EXTERNAL_URL}" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# Set SEED_ON_DEPLOY=true on first deploy to load demo data + users
if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
