# Stage 1: Build frontend assets
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --ignore-scripts
COPY . .
RUN npm run build

# Stage 2: PHP runtime with Nginx + Supervisor
FROM php:8.3-fpm

# Install system dependencies, Nginx, Supervisor, Cron
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libwebp-dev \
    libzip-dev \
    libicu-dev \
    libbz2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libgmp-dev \
    libldap-dev \
    zlib1g-dev \
    unzip \
    git \
    cron \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mysqli bcmath bz2 calendar exif \
    fileinfo gd gettext gmp intl ldap mbstring opcache \
    pcntl soap sockets zip

# Copy project files
COPY . /var/www/html
WORKDIR /var/www/html

# Copy built frontend assets from node-builder
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Build-time env for artisan commands
RUN echo "APP_KEY=base64:buildtimeplaceholder12345678901234567890==" > /var/www/html/.env \
    && echo "DB_CONNECTION=mysql" >> /var/www/html/.env \
    && echo "APP_ENV=production" >> /var/www/html/.env \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan storage:link \
    && rm /var/www/html/.env

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/storage
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/storage

# Laravel scheduler cron
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel \
    && chmod 0644 /etc/cron.d/laravel \
    && crontab /etc/cron.d/laravel

# Remove default nginx config
RUN rm -f /etc/nginx/sites-enabled/default

# Copy custom configs
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
