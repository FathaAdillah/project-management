FROM php:8.3-cli-alpine

# Install system dependencies (runtime only)
RUN apk add --no-cache \
    libpng \
    oniguruma \
    libxml2 \
    icu-libs \
    libzip \
    libcap \
    curl \
    bash

# Install build dependencies temporarily
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    # Install PHP extensions \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    sockets \
    # Remove build dependencies \
    && apk del .build-deps \
    # Clean up \
    && rm -rf /tmp/* /var/cache/apk/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install FrankenPHP for Octane
COPY --from=dunglas/frankenphp:latest /usr/local/bin/frankenphp /usr/local/bin/frankenphp
RUN setcap 'cap_net_bind_service=+ep' /usr/local/bin/frankenphp

# Set working directory
WORKDIR /var/www

# Copy application files
COPY --chown=www-data:www-data . .

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy entrypoint script
COPY --chmod=755 entrypoint.sh /usr/local/bin/entrypoint.sh

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Switch to non-root user
USER www-data

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
