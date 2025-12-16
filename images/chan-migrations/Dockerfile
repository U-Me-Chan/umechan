FROM library/php:8.4.12-alpine

RUN apk add zip libzip-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN docker-php-source delete

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ADD chan-migrations .
RUN composer install

CMD ["php", "./vendor/bin/phinx", "migrate"]
