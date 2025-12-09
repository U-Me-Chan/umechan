FROM library/php:8.4-cli
WORKDIR /scheduler

RUN apt update -yyq && apt install -yyq zip libzip-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip sockets
RUN docker-php-source delete

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php

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
