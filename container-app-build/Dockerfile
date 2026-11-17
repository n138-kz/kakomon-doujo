FROM php:8.5-fpm-alpine

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk --no-cache add \
    postgresql-dev \
    && docker-php-ext-install pdo_pgsql

# GDとFreeTypeの依存パッケージをインストールし、有効化
RUN apk add --no-cache \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN (delgroup $(getent group 983 | cut -d: -f1) || true) \
    && addgroup -g 983 -S docker \
    && addgroup www-data docker

RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/hide-php-version.ini
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/post-size-extention.ini
RUN echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/post-size-extention.ini

WORKDIR /var/www/html
