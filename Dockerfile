FROM php:8.3-cli-alpine

# Install dependencies
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    zip unzip git curl \
    freetype-dev libjpeg-turbo-dev \
    autoconf g++ make

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

RUN apk add --no-cache \
    openssl-dev \
    pkgconfig \
    linux-headers

# Install swoole
RUN pecl install swoole \
    && docker-php-ext-enable swoole
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

# permission
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
