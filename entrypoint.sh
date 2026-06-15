#!/bin/bash
set -e

cd /var/www/html

# Run migrations if DB is available
if php artisan migrate --force 2>/dev/null; then
    echo "✅ Migrations completed"
else
    echo "⚠️  Migrations skipped (DB not ready or already up to date)"
fi

# Cache config
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# Start Apache
apache2-foreground
