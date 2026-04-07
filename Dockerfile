# ========================
# 1. Composer dependencies
# ========================
FROM composer:2.8 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# ========================
# 2. Node build (Vite)
# ========================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy vendor dari composer stage (needed for Filament CSS)
COPY --from=composer-builder /app/vendor ./vendor

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ========================
# 3. PHP final image
# ========================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    zip unzip git curl \
    freetype-dev libjpeg-turbo-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy app source
COPY --chown=www-data:www-data . .

# Copy vendor dari composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy Vite build
COPY --from=node-builder /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
