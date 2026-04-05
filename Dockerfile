FROM php:8.2-fpm-alpine

# Use an empty SERVER_NAME to disable Caddy's automatic HTTPS formatting if needed, 
# although we'll use a more standard approach with PHP-FPM for pure stability if preferred.
# But since you requested FrankenPHP, let's use the official Dunglas FrankenPHP image instead.
FROM dunglas/frankenphp:1.4-php8.2-alpine

# Set port to 80 for internal communication (Caddy on the host will proxy to this)
ENV SERVER_NAME=":80"

# Install system dependencies needed for Laravel and standard web functionality
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nodejs \
    npm

# Use the install-php-extensions script included in the frankenphp image
# This automatically handles all the underlying dependencies
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    bcmath \
    pcntl \
    exif \
    opcache

# Copy project files into the container
COPY . /app
WORKDIR /app

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (ignoring dev packages for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets using Vite
RUN npm install && npm run build

# Ensure correct permissions for Laravel storage and cache directories
# FrankenPHP Alpine image usually runs properly with these wide open inside the container
RUN chmod -R 777 /app/storage /app/bootstrap/cache

# Copy production PHP configuration
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

# Expose the internal port
EXPOSE 80
