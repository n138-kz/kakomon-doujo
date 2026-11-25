FROM php:8.5-fpm-alpine

# Nginx、ImageMagick本体、HEIC対応ライブラリ、およびImagick拡張のビルドに必要な最小限のパッケージをインストール
RUN apk add --no-cache \
    nginx \
    imagemagick \
    imagemagick-dev \
    imagemagick-heic \
    libheif \
    libwebp \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps

# Nginxの設定ファイルをコピー
COPY nginx.conf /etc/nginx/nginx.conf

# アプリケーションコードの配置先ディレクトリ
WORKDIR /var/www/html
COPY html/ /var/www/html/

# 権限の調整
RUN chown -R www-data:www-data /var/www/html /var/lib/nginx

# ポートの公開
EXPOSE 80

# PHP-FPMとNginxを同時に起動するスクリプト
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]