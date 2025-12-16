FROM library/php:8.4.12-alpine
WORKDIR /db-importer

RUN apk add zip libzip-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN docker-php-source delete

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY radio-db-importer .
RUN composer install

ARG MYSQL_DATABASE
ARG MYSQL_HOSTNAME
ARG MYSQL_USERNAME
ARG MYSQL_PASSWORD
ENV MYSQL_DATABASE $MYSQL_DATABASE
ENV MYSQL_HOSTNAME $MYSQL_HOSTNAME
ENV MYSQL_USERNAME $MYSQL_USERNAME
ENV MYSQL_PASSWORD $MYSQL_PASSWORD

CMD php ./index.php
