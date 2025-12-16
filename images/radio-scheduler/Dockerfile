FROM library/php:8.4.12-alpine
WORKDIR /scheduler

RUN apk add zip libzip-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip sockets
RUN docker-php-source delete

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY radio-scheduler .
RUN composer install --no-dev

ARG MPD_HOSTNAME
ARG MPD_PORT
ENV MPD_HOSTNAME $MPD_HOSTNAME
ENV MPD_PORT $MPD_PORT

ARG MYSQL_DATABASE
ARG MYSQL_HOSTNAME
ARG MYSQL_USERNAME
ARG MYSQL_PASSWORD
ENV MYSQL_DATABASE $MYSQL_DATABASE
ENV MYSQL_HOSTNAME $MYSQL_HOSTNAME
ENV MYSQL_USERNAME $MYSQL_USERNAME
ENV MYSQL_PASSWORD $MYSQL_PASSWORD

EXPOSE 8080 8080

CMD php ./index.php
