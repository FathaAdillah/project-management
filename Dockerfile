# ==============================
# 1. Base Image
# ==============================
FROM php:8.3-cli-alpine AS base

RUN apk add --no-cache \
    bash curl git unzip \
    libpng libjpeg-turbo freetype \
    oniguruma libxml2 icu-libs libzip \
    netcat-openbsd

WORKDIR /var/www

# ==============================
# 2. PHP Extensions
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
# 3. Composer Install
# ==============================
FROM base AS composer

COPY --from=php-build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# cache layer
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-scripts

# copy full app
COPY . .

RUN composer dump-autoload --optimize

# ==============================
# 4. Node Build (Vite)
# ==============================
FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js* ./
COPY postcss.config.js* ./

# needed for Filament
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# FAIL FAST kalau manifest tidak ada
RUN test -f public/build/manifest.json

# ==============================
# 5. Final Image
# ==============================
FROM base AS production

COPY --from=php-build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# copy app
COPY --from=composer /app ./

# copy assets
COPY --from=node /app/public/build ./public/build

# permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ==============================
# Entrypoint (FIX SEMUA ERROR)
# ==============================
RUN echo '#!/bin/sh' > /entrypoint.sh \
 && echo 'set -e' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'echo "🚀 Starting Laravel..."' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Ensure .env exists' >> /entrypoint.sh \
 && echo 'if [ ! -f ".env" ]; then cp .env.example .env; fi' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Generate key' >> /entrypoint.sh \
 && echo 'php artisan key:generate --force || true' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Run package discover (FIX ERROR COMPOSER)' >> /entrypoint.sh \
 && echo 'php artisan package:discover --ansi || true' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Storage link' >> /entrypoint.sh \
 && echo 'php artisan storage:link || true' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Cache for production' >> /entrypoint.sh \
 && echo 'php artisan config:cache' >> /entrypoint.sh \
 && echo 'php artisan route:cache' >> /entrypoint.sh \
 && echo 'php artisan view:cache' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo '# Check Vite manifest' >> /entrypoint.sh \
 && echo 'if [ ! -f "public/build/manifest.json" ]; then' >> /entrypoint.sh \
 && echo '  echo "❌ Vite manifest missing!"' >> /entrypoint.sh \
 && echo '  exit 1' >> /entrypoint.sh \
 && echo 'fi' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'echo "✅ App ready"' >> /entrypoint.sh \
 && echo '' >> /entrypoint.sh \
 && echo 'exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=${PORT:-8000}' >> /entrypoint.sh \
 && chmod +x /entrypoint.sh

USER www-data

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8000

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
