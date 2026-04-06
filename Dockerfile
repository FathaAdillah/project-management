# Multi-stage build for smaller image size
FROM php:8.3-cli-alpine AS base

# Install runtime dependencies
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

# Build stage
FROM base AS build

# Install build dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    linux-headers \
    openssl-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    sockets

# Install Swoole
RUN pecl install swoole \
    && docker-php-ext-enable swoole \
    && pecl clear-cache

# Remove build dependencies
RUN apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Final stage
FROM base AS production

# Copy PHP extensions from build stage
COPY --from=build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY --chown=www-data:www-data . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Create entrypoint script
RUN echo '#!/bin/sh' > /entrypoint.sh \
    && echo 'set -e' >> /entrypoint.sh \
    && echo '' >> /entrypoint.sh \
    && echo 'echo "🚀 Laravel Octane starting..."' >> /entrypoint.sh \
    && echo '' >> /entrypoint.sh \
    && echo '# Wait for database' >> /entrypoint.sh \
    && echo 'if [ ! -z "$DB_HOST" ]; then' >> /entrypoint.sh \
    && echo '    echo "⏳ Waiting for database..."' >> /entrypoint.sh \
    && echo '    timeout 30 sh -c "until nc -z \$DB_HOST \${DB_PORT:-3306} 2>/dev/null; do sleep 1; done" || true' >> /entrypoint.sh \
    && echo 'fi' >> /entrypoint.sh \
    && echo '' >> /entrypoint.sh \
    && echo '# Run migrations' >> /entrypoint.sh \
    && echo 'if [ "$RUN_MIGRATIONS" = "true" ]; then' >> /entrypoint.sh \
    && echo '    echo "📊 Running migrations..."' >> /entrypoint.sh \
    && echo '    php artisan migrate --force --no-interaction || true' >> /entrypoint.sh \
    && echo 'fi' >> /entrypoint.sh \
    && echo '' >> /entrypoint.sh \
    && echo '# Cache optimization' >> /entrypoint.sh \
    && echo 'if [ "$APP_ENV" = "production" ]; then' >> /entrypoint.sh \
    && echo '    echo "⚡ Optimizing..."' >> /entrypoint.sh \
    && echo '    php artisan config:cache' >> /entrypoint.sh \
    && echo '    php artisan route:cache' >> /entrypoint.sh \
    && echo '    php artisan view:cache' >> /entrypoint.sh \
    && echo 'fi' >> /entrypoint.sh \
    && echo '' >> /entrypoint.sh \
    && echo 'echo "✅ Starting Octane on http://0.0.0.0:${PORT:-8000}"' >> /entrypoint.sh \
    && echo 'exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=${PORT:-8000}' >> /entrypoint.sh \
    && chmod +x /entrypoint.sh

# Switch to non-root user
USER www-data

# Environment variables
ENV OCTANE_SERVER=swoole
ENV PORT=8000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8000}/api/health || exit 1

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
