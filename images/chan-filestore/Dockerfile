FROM library/php:8.4.12-fpm-alpine AS base
RUN apk add --no-cache --virtual zip libzip-dev autoconf g++ make linux-headers imagemagick-dev ffmpegthumbnailer && \
  docker-php-ext-install -j$(nproc) pdo_mysql zip && \
  pecl install imagick && \
  docker-php-source delete && \
  apk del --purge autoconf g++ make linux-headers

ARG MAX_FILESIZE
COPY images/chan-filestore/filestore-php.ini /usr/local/etc/php/conf.d
RUN sed -i "s/:max_filesize:/${MAX_FILESIZE}/g" /usr/local/etc/php/conf.d/filestore-php.ini

FROM composer:latest AS filestore-deps
COPY chan-filestore/composer.json .
COPY chan-filestore/composer.lock .
RUN composer install \
  --no-dev \
  --no-interaction \
  --no-plugins \
  --no-scripts \
  --prefer-dist \
  --ignore-platform-reqs

FROM base AS development
WORKDIR /var/www/html

FROM base AS production
WORKDIR /var/www/html
COPY images/chan-filestore/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
COPY chan-filestore/index.php .
COPY chan-filestore/src ./src
COPY --from=filestore-deps /app/vendor ./vendor
