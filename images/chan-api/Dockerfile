FROM library/php:8.4-fpm AS base

RUN apt update -yyq && apt install -yyq zip libzip-dev libmemcached-dev libssl-dev
RUN pecl install memcached
RUN docker-php-ext-enable memcached
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN docker-php-source delete

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php

ADD images/chan-api/api-php.ini /usr/local/etc/php/conf.d

FROM base AS development
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
ADD images/chan-api/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

FROM base AS production
ADD images/chan-api/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
WORKDIR /var/www/html
ADD chan-api .
RUN composer install
