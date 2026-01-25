FROM php:8.2-cli

# Install common extensions and tools used in tests and by composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip zlib1g-dev libzip-dev libxml2-dev \
    && docker-php-ext-install zip pcntl

# Xdebug for coverage (optional)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/project
