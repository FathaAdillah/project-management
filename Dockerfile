# ==============================
# 1. Base PHP Image
# ==============================
FROM php:8.3-cli-alpine AS base

RUN apk add --no-cache \
    bash curl git unzip \
    libpng libjpeg-turbo freetype \
    oniguruma libxml2 icu-libs libzip \
    netcat-openbsd

WORKDIR /var/www

# ==============================
# 2. PHP Extensions Build
# ==============================
FROM base AS php-build

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    oniguruma-dev libxml2-dev icu-dev libzip-dev \
    linux-headers openssl-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring pcntl bcmath gd intl zip sockets

RUN pecl install swoole \
    && docker-php-ext-enable swoole

RUN apk del .build-deps && rm -rf /tmp/*

# ==============================
# 3. Composer Dependencies
# ==============================
FROM base AS composer

COPY --from=php-build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# copy only needed files first (cache optimization)
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# then copy full app
COPY . .

RUN composer dump-autoload --optimize

# ==============================
# 4. Node Build (Vite)
# ==============================
FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

# IMPORTANT: copy all needed files
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js* ./
COPY postcss.config.js* ./

# needed for Filament scanning
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# sanity check (VERY IMPORTANT)
RUN test -f public/build/manifest.json

# ==============================
# 5. Final Production Image
# ==============================
FROM base AS production

# copy PHP extensions
COPY --from=php-build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# copy composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# copy app
COPY --from=composer /app ./

# copy built assets
COPY --from=node /app/public/build ./public/build

# permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ==============================
# Entrypoint
# ==============================
RUN echo '#!/bin/sh' > /entrypoint.sh \
 && echo 'set -e' >> /entrypoint.sh \
 && echo 'echo "🚀 Starting Laravel Octane..."' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'if [ ! -f "public/build/manifest.json" ]; then' >> /entrypoint.sh \
 && echo '  echo "❌ Vite manifest missing!"' >> /entrypoint.sh \
 && echo '  exit 1' >> /entrypoint.sh \
 && echo 'fi' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'php artisan config:cache' >> /entrypoint.sh \
 && echo 'php artisan route:cache' >> /entrypoint.sh \
 && echo 'php artisan view:cache' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=${PORT:-8000}' >> /entrypoint.sh \
 && chmod +x /entrypoint.sh

USER www-data

ENV APP_ENV=production
ENV PORT=8000

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
