FROM library/php:8.4.12-alpine

RUN apk add zip libzip-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN docker-php-source delete

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD chan-migrations .
RUN composer install

CMD ["php", "./vendor/bin/phinx", "migrate"]
