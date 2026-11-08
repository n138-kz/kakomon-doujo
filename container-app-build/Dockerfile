FROM php:8.5-fpm-alpine

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk --no-cache add \
    postgresql-dev \
    && docker-php-ext-install pdo_pgsql

RUN (delgroup $(getent group 983 | cut -d: -f1) || true) \
    && addgroup -g 983 -S docker \
    && addgroup www-data docker

RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/hide-php-version.ini

WORKDIR /var/www/html
