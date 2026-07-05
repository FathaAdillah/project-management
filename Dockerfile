FROM php:8.3-fpm

# Update Debian mirror
RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Working directory
WORKDIR /var/www

# Copy seluruh source code
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# Install Node dependencies & build Vite
RUN if [ -f package.json ]; then \
    npm install && npm run build; \
    fi

# Laravel optimization (jangan gagal kalau APP_KEY belum ada)
RUN php artisan storage:link || true
RUN php artisan optimize || true

# Permission
RUN chown -R www-data:www-data /var/www

# Clean cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

EXPOSE 9000

CMD ["php-fpm"]
