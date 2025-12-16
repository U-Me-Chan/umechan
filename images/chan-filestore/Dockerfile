FROM library/php:8.4.12-fpm-alpine AS base

RUN apk add zip libzip-dev autoconf g++ make linux-headers imagemagick-dev ffmpegthumbnailer
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN pecl install imagick
RUN docker-php-source delete
RUN apk del --purge autoconf g++ make linux-headers

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ARG MAX_FILESIZE
COPY images/chan-filestore/filestore-php.ini /usr/local/etc/php/conf.d
RUN sed -i "s/:max_filesize:/${MAX_FILESIZE}/g" /usr/local/etc/php/conf.d/filestore-php.ini

FROM base AS production
WORKDIR /var/www/html
COPY images/chan-filestore/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
COPY chan-filestore .
RUN composer install
