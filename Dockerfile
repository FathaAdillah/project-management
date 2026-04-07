# ==============================================
# Stage 1: Build PHP Dependencies First
# ==============================================
FROM php:8.3-cli-alpine AS vendor-builder

# Install minimal dependencies for Composer
RUN apk add --no-cache git unzip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (no scripts needed, just vendor files)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ==============================================
# Stage 2: Build Frontend Assets with Node.js
# ==============================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy vendor from vendor-builder (needed for Filament theme.css)
COPY --from=vendor-builder /app/vendor ./vendor

# Copy package files
COPY package.json package-lock.json* ./

# Install node dependencies
RUN npm ci --no-audit --prefer-offline

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY app ./app

# Build production assets
RUN npm run build

# ==============================================
# Stage 3: Build Full PHP Application
# ==============================================
FROM php:8.3-cli-alpine AS php-builder

# Install system dependencies and build tools
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libxml2 \
    icu-libs \
    libzip \
    && apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    autoconf \
    g++ \
    make \
    openssl-dev \
    pkgconfig \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Install Swoole
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Remove build dependencies
RUN apk del .build-deps

# ==============================================
# Stage 4: Final Production Image
# ==============================================
FROM php:8.3-cli-alpine

# Install runtime dependencies only
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libxml2 \
    icu-libs \
    libzip \
    curl \
    bash

# Copy PHP extensions from builder
COPY --from=php-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "opcache.enable=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.memory_consumption=256" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.interned_strings_buffer=16" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.fast_shutdown=1" >> "$PHP_INI_DIR/conf.d/opcache.ini"

# Create application user
RUN addgroup -g 1000 laravel \
    && adduser -u 1000 -G laravel -s /bin/sh -D laravel

WORKDIR /var/www

# Copy vendor from vendor-builder (not from php-builder to avoid redundancy)
COPY --from=vendor-builder --chown=laravel:laravel /app/vendor ./vendor

# Copy built frontend assets from frontend-builder
COPY --from=frontend-builder --chown=laravel:laravel /app/public/build ./public/build

# Copy application files
COPY --chown=laravel:laravel . .

# Install Composer for autoload generation
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && rm /usr/bin/composer

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache

# Switch to non-root user
USER laravel

EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/api/health || exit 1

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
