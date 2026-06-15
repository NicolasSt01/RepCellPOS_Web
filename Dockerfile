FROM composer:latest AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

FROM php:8.3-apache AS final

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring gd zip \
    && a2enmod rewrite

COPY --from=vendor /app/vendor /var/www/html/vendor
COPY . /var/www/html
COPY entrypoint.sh /entrypoint.sh

WORKDIR /var/www/html

RUN mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && cp .env.example .env \
    && php artisan storage:link \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache /entrypoint.sh \
    && rm -f .env

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
