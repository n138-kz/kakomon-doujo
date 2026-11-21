FROM php:8.5-fpm-alpine

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk --no-cache add \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        zip

# GDとFreeTypeの依存パッケージをインストールし、有効化
RUN apk add --no-cache \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# ImageMagick と PHP Imagick 拡張のインストール（PNG -> PDF変換用）
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        imagemagick-dev \
    && apk add --no-cache \
        imagemagick \
        ghostscript \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps \
    && find /etc/ImageMagick* -name "policy.xml" -exec sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/g' {} +

RUN apk add --no-cache \
        fontconfig \
        font-noto-cjk \
    && fc-cache -fv

RUN (delgroup $(getent group 983 | cut -d: -f1) || true) \
    && addgroup -g 983 -S docker \
    && addgroup www-data docker

RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/hide-php-version.ini
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/post-size-extention.ini
RUN echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/post-size-extention.ini

WORKDIR /var/www/html
