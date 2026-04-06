FROM dunglas/frankenphp:1.4-php8.2-alpine

# Set port to 80 for internal communication
ENV SERVER_NAME=":80"

# Install system dependencies needed for Laravel
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nodejs \
    npm

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    bcmath \
    pcntl \
    exif \
    opcache

# Copy composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory (source code will be mounted from host)
WORKDIR /app

# Ensure correct permissions for Laravel storage
RUN mkdir -p /app/storage /app/bootstrap/cache
RUN chmod -R 777 /app/storage /app/bootstrap/cache

# Copy production PHP configuration
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

EXPOSE 80
