#!/usr/bin/env bash
set -e

cd /var/www/html

# The SQLite file and all writable state live under storage/, which is the
# one directory mounted as a persistent volume — so it survives every deploy
# even though the rest of the image is replaced wholesale each time.
mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
touch storage/app/database.sqlite
chown -R www-data:www-data storage bootstrap/cache

if [ -z "$APP_KEY" ]; then
    echo "FATAL: APP_KEY is not set. Create the server's .env file first (see DEPLOY.md)." >&2
    exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
