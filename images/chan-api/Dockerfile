FROM library/php:8.4.12-fpm-alpine AS base

RUN apk add zip libzip-dev libmemcached-dev libressl-dev autoconf g++ make linux-headers
RUN pecl install memcached
RUN docker-php-ext-enable memcached
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN docker-php-source delete

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ADD images/chan-api/api-php.ini /usr/local/etc/php/conf.d

FROM base AS development
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN apk del --purge autoconf g++ make linux-headers
ADD images/chan-api/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

FROM base AS production
RUN apk del --purge autoconf g++ make linux-headers
ADD images/chan-api/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
WORKDIR /var/www/html
ADD chan-api .
RUN composer install
