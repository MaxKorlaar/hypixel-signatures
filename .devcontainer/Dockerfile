FROM mcr.microsoft.com/devcontainers/php:1-8.2-bookworm

# Install MariaDB client and other dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client libpng-dev libsodium-dev libwebp-dev libfreetype6-dev \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Install the PHP extensions we need
RUN docker-php-ext-configure gd \
    --with-webp \
    --with-freetype \
    && docker-php-ext-install gd mysqli pdo pdo_mysql sodium pcntl

# Enable redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Configure Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    # && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini
