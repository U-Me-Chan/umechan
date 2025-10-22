FROM library/php:8.4

RUN apt update -yyq && apt install -yyq zip libzip-dev
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-source delete

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename composer; rm composer-setup.php

ADD chan-migrations .
RUN composer install

CMD ["php", "./vendor/bin/phinx", "migrate"]
