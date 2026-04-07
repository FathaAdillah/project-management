# ========================
# 1. Composer dependencies
# ========================
FROM php:8.3-cli-alpine AS composer-builder

# Install dependencies
RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    libzip-dev \
    zip unzip git curl

# Install PHP extensions (🔥 penting untuk Filament & Excel)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd mbstring zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
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

# Copy vendor dari composer stage (PENTING untuk Filament CSS!)
COPY --from=composer-builder /app/vendor ./vendor

# Install dependencies
COPY package*.json ./
RUN npm ci

# Copy needed files for Vite build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY app ./app

# Build Vite assets
RUN npm run build


# ========================
# 3. PHP-FPM final image
# ========================
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip unzip git curl

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application source
COPY --chown=www-data:www-data . .

# Copy vendor dari composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy Vite build
COPY --from=node-builder /app/public/build ./public/build

# Set permission
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
