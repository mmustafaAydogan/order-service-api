FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git\
    curl\
    zip\
    unzip\
    libzip-dev\
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html