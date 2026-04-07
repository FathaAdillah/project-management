# Build stage for Node.js assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Install PHP and Composer (needed for vendor dependencies)
RUN apk add --no-cache php82 php82-phar php82-mbstring php82-openssl php82-tokenizer php82-fileinfo php82-json curl git unzip
RUN curl -sS https://getcomposer.org/installer | php82 -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files and install dependencies first
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# Copy package files
COPY package*.json ./

# Install npm dependencies
RUN npm ci

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY app ./app

# Build assets
RUN npm run build

# PHP-FPM stage
FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    freetype-dev \
    libjpeg-turbo-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY --chown=www-data:www-data . /var/www

# Copy built assets from node-builder stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build /var/www/public/build

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
