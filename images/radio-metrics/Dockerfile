FROM library/php:8.2-cli
WORKDIR /usr/local/share/metrics

RUN apt update -yyq && apt install -yyq zip libzip-dev libmemcached-dev zlib1g-dev libssl-dev
RUN pecl install memcached
RUN docker-php-ext-enable memcached
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install sockets
RUN docker-php-source delete

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php

COPY radio-metrics .
RUN composer install

ARG MYSQL_DATABASE
ARG MYSQL_HOSTNAME
ARG MYSQL_USERNAME
ARG MYSQL_PASSWORD
ARG RADIO_API_URL
ARG RADIO_CHAN_API_URL
ARG RADIO_CHAN_THREAD_ID
ARG RADIO_CHAN_POSTER_KEY
ARG RADIO_DISCORD_HOOK_URL
ARG RADIO_LOG_LEVEL
ARG MPD_HOSTNAME
ARG MPD_PORT
ENV MYSQL_DATABASE $MYSQL_DATABASE
ENV MYSQL_HOSTNAME $MYSQL_HOSTNAME
ENV MYSQL_USERNAME $MYSQL_USERNAME
ENV MYSQL_PASSWORD $MYSQL_PASSWORD
ENV RADIO_API_URL $RADIO_API_URL
ENV RADIO_CHAN_API_URL $RADIO_CHAN_API_URL
ENV RADIO_CHAN_THREAD_ID $RADIO_CHAN_THREAD_ID
ENV RADIO_CHAN_POSTER_KEY $RADIO_CHAN_POSTER_KEY
ENV RADIO_DISCORD_HOOK_URL $RADIO_CHAN_HOOK_KEY
ENV RADIO_LOG_LEVEL $RADIO_LOG_LEVEL
ENV MPD_HOSTNAME $MPD_HOSTNAME
ENV MPD_PORT $MPD_PORT
ENV MPD_DATABASE_PATH /var/lib/music

EXPOSE 8080 8080

CMD php ./index.php
