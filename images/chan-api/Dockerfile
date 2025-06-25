FROM library/php:8.4-fpm AS base
RUN apt update -yyq && apt install -yyq zip libzip-dev
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-source delete
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php
COPY images/chan-api/api-php.ini /usr/local/etc/php/conf.d

FROM base AS development
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

FROM base AS production
COPY images/chan-api/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
WORKDIR /var/www/html
COPY chan-api .
RUN composer install
