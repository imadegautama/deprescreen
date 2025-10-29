FROM php:8.3-fpm-alpine

# System deps (runtime)
RUN apk add --no-cache \
    git curl bash zip unzip shadow supervisor \
    icu-dev libzip-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev libxml2-dev

# PHP extensions bawaan
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd intl zip pdo_mysql bcmath pcntl opcache

# ===== FIX PECL REDIS: butuh toolchain ($PHPIZE_DEPS) =====
# Pakai virtual package .build-deps biar nanti bisa dihapus
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps
# =========================================================

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php.ini & supervisor
COPY docker/php/php.ini /usr/local/etc/php/php.ini
# Jika kamu pakai file utama supervisord.conf:
COPY docker/php/supervisord.conf /etc/supervisord.conf
# Atau jika pakai file program terpisah (pilih salah satu):
# COPY docker/php/queue-worker.ini /etc/supervisor.d/queue-worker.ini

# Samakan UID/GID agar file di host tidak jadi root
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data

WORKDIR /var/www/html
