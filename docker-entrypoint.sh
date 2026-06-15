#!/bin/sh
set -e

# Wait for database
echo "Waiting for database..."
for i in $(seq 1 30); do
    if php -r "new PDO('mysql:host=${DB_HOST:-db};dbname=${DB_DATABASE:-repcellpos}', '${DB_USERNAME:-repcellpos}', '${DB_PASSWORD}');" 2>/dev/null; then
        echo "Database ready."
        break
    fi
    echo "Attempt $i/30 - waiting for database..."
    sleep 2
done

# Run migrations once using a lock file
if [ ! -f /var/www/html/storage/framework/migrated.lock ]; then
    php artisan migrate --force
    php artisan storage:link --force

    # Seed only in non-production or when APP_ENV is not production
    if [ "${APP_ENV}" != "production" ]; then
        php artisan db:seed --force --no-interaction 2>/dev/null || true
    fi

    touch /var/www/html/storage/framework/migrated.lock
fi

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
