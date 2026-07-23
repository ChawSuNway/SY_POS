#!/bin/sh
set -e

# Render က PORT env var ထိုးပေးသည် — Apache ကို ထို port သို့ ချိန်
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# APP_URL မသတ်မှတ်ထားလျှင် Render ၏ external URL ကို သုံး
if [ -z "${APP_URL}" ] && [ -n "${RENDER_EXTERNAL_URL}" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# ပထမဆုံး deploy တွင် SEED_ON_DEPLOY=true ထား၍ demo data + users သွင်းနိုင်သည်
if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
