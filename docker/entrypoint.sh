#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/app/public storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache

# Persistent APP_KEY fallback (stored in the app_storage volume)
if [ -z "$APP_KEY" ]; then
    if [ ! -f storage/app/.app_key ]; then
        echo "base64:$(head -c 32 /dev/urandom | base64)" > storage/app/.app_key
        echo "Generated new APP_KEY (stored in storage/app/.app_key)"
    fi
    export APP_KEY="$(cat storage/app/.app_key)"
fi

# Wait for the database (max ~60s)
DB_PORT="${DB_PORT:-3306}"
echo "Waiting for database ${DB_HOST}:${DB_PORT}..."
tries=0
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0);} catch (Exception \$e) { exit(1);}"; do
    tries=$((tries + 1))
    if [ "$tries" -ge 30 ]; then
        echo "ERROR: database ${DB_HOST}:${DB_PORT} unreachable after 60s" >&2
        exit 1
    fi
    sleep 2
done
echo "Database is up."

php artisan package:discover --ansi
php artisan optimize:clear
php artisan storage:link 2>/dev/null || true

if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"