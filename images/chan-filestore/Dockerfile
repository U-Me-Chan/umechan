FROM library/php:8.4-fpm AS base
RUN apt update -yyq && apt install -yyq zip libzip-dev libmagickwand-dev ffmpegthumbnailer
RUN docker-php-ext-install zip
RUN pecl install imagick
RUN docker-php-ext-install pdo_mysql
RUN docker-php-source delete
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php
ARG MAX_FILESIZE
COPY images/chan-filestore/filestore-php.ini /usr/local/etc/php/conf.d
RUN sed -i "s/:max_filesize:/${MAX_FILESIZE}/g" /usr/local/etc/php/conf.d/filestore-php.ini

FROM base AS production
WORKDIR /var/www/html
COPY images/chan-filestore/php-logging.conf /usr/local/etc/php-fmp.d/logging.conf
COPY chan-filestore .
RUN composer install
