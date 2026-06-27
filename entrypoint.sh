#!/bin/bash
set -e

cd /var/www/html

# Always regenerate .env from environment variables on every start
cat > .env << EOF
APP_NAME=${APP_NAME:-RepCellPOS}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
APP_LOCALE=${APP_LOCALE:-es}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE:-es}
APP_FAKER_LOCALE=${APP_FAKER_LOCALE:-es_MX}
APP_MAINTENANCE_DRIVER=${APP_MAINTENANCE_DRIVER:-file}
BCRYPT_ROUNDS=${BCRYPT_ROUNDS:-12}
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_STACK=${LOG_STACK:-single}
LOG_DEPRECATIONS_CHANNEL=${LOG_DEPRECATIONS_CHANNEL:-null}
LOG_LEVEL=${LOG_LEVEL:-error}
DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-RepCellPOS}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD}
SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_ENCRYPT=${SESSION_ENCRYPT:-false}
SESSION_PATH=${SESSION_PATH:-/}
SESSION_DOMAIN=${SESSION_DOMAIN:-null}
BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
CACHE_STORE=${CACHE_STORE:-database}
REDIS_CLIENT=${REDIS_CLIENT:-phpredis}
REDIS_HOST=${REDIS_HOST:-redis}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
MAIL_MAILER=${MAIL_MAILER:-log}
MAIL_HOST=${MAIL_HOST:-smtp.gmail.com}
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@repcellpos.nexacore.com.mx}
MAIL_FROM_NAME="${MAIL_FROM_NAME:-RepCellPOS}"
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}
R2_ACCESS_KEY_ID=${R2_ACCESS_KEY_ID}
R2_SECRET_ACCESS_KEY=${R2_SECRET_ACCESS_KEY}
R2_BUCKET=${R2_BUCKET}
R2_ENDPOINT=${R2_ENDPOINT}
R2_PUBLIC_URL=${R2_PUBLIC_URL}
EOF

# Clear all caches before rebuilding them
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Run migrations if DB is available
if php artisan migrate --force 2>/dev/null; then
    echo "✅ Migrations completed"
else
    echo "⚠️  Migrations skipped (DB not ready or already up to date)"
fi

# Rebuild caches with fresh config
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# Start Apache
apache2-foreground
