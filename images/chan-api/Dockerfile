FROM library/php:8.4.12-fpm-alpine AS base
RUN apk add --no-cache --virtual \
  zip \
  libzip-dev \
  libmemcached-dev \
  autoconf \
  g++ \
  make \
  librdkafka \
  librdkafka-dev \
  linux-headers && \
  pecl install memcached && \
  pecl install rdkafka && \
  docker-php-ext-enable memcached && \
  docker-php-ext-install -j$(nproc) pdo_mysql zip && \
  docker-php-source delete
COPY images/chan-api/api-php.ini /usr/local/etc/php/conf.d
COPY images/chan-api/www.conf /usr/local/etc/php-fpm.d/www.conf

FROM composer:latest AS deps
COPY chan-api/composer.json .
COPY chan-api/composer.lock .
RUN composer install \
  --ignore-platform-reqs \
  --no-interaction \
  --no-plugins \
  --no-scripts \
  --prefer-dist \
  --no-dev

FROM base AS development
RUN pecl install xdebug && \
  docker-php-ext-enable xdebug && \
  apk del --purge \
  autoconf \
  g++ \
  make \
  linux-headers
COPY images/chan-api/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

FROM base AS production
RUN apk del --purge \
  autoconf \
  g++ \
  make \
  linux-headers
COPY images/chan-api/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
WORKDIR /var/www/html
COPY --from=deps /app/vendor ./vendor
COPY chan-api/index.php .
COPY chan-api/config.php .
COPY chan-api/src ./src
