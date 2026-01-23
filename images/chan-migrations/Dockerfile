FROM library/php:8.4.12-alpine AS base
RUN apk add zip libzip-dev && \
  docker-php-ext-install -j$(nproc) pdo_mysql zip && \
  docker-php-source delete

FROM composer:latest AS deps
COPY chan-migrations/composer.json .
COPY chan-migrations/composer.lock .
RUN composer install \
  --ignore-platform-reqs \
  --no-interaction \
  --no-plugins \
  --no-scripts \
  --prefer-dist \
  --no-dev

FROM base AS runner
WORKDIR /tmp
COPY --from=deps /app/vendor ./vendor
COPY chan-migrations/migrations ./migrations
COPY chan-migrations/phinx.php .
CMD ["php", "/tmp/vendor/bin/phinx", "migrate"]
