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
    bash \
    freetype \
    libjpeg-turbo

# Install build dependencies temporarily
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    linux-headers \
    openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    sockets \
    # Install Swoole for Octane \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
