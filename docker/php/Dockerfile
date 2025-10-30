# ===== STAGE 1: PHP Setup (Composer Install) =====
FROM php:8.3-fpm-alpine AS php-setup

WORKDIR /app

# Install only composer & basic tools needed for composer/artisan
RUN apk add --no-cache git curl bash unzip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# ===== STAGE 2: Frontend Build =====
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy frontend source
COPY resources ./resources
COPY vite.config.ts tsconfig.json ./
COPY config ./config
COPY app ./app
COPY routes ./routes

# Copy PHP files (composer:generate may need it)
COPY --from=php-setup /app/vendor ./vendor
COPY --from=php-setup /app/bootstrap ./bootstrap
COPY artisan ./

# Build frontend assets (production build)
# wayfinder plugin may call php artisan — ensure php available
RUN apk add --no-cache php php-cli php-json php-mbstring \
    && npm run build \
    && apk del php php-cli php-json php-mbstring

# ===== STAGE 3: PHP Runtime =====
FROM php:8.3-fpm-alpine

# System deps (runtime)
RUN apk add --no-cache \
    git curl bash zip unzip shadow supervisor \
    nano vim \
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

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy pre-built vendor dari Stage 1
COPY --from=php-setup /app/vendor ./vendor

# Copy pre-built frontend assets dari Stage 2
COPY --from=frontend-builder /app/public/build ./public/build

# Fix ownership (for vendor/ & public/)
RUN chown -R www-data:www-data /var/www/html

USER www-data

# Copy entrypoint script (runs as root before switching to www-data)
COPY --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
