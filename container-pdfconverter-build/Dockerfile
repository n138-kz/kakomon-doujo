FROM php:8.5-fpm-alpine

# Nginx、ImageMagick本体、HEIC対応ライブラリ、およびImagick拡張のビルドに必要な最小限のパッケージをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
    nginx \
    # ビルド・ランタイム共通の依存パッケージ
    ghostscript \
    qpdf \
    libzip \
    imagemagick \
    # ビルド時のみ必要なパッケージ（--virtualでまとめて削除可能に）
    $PHPIZE_DEPS \
    libzip-dev \
    imagemagick-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install zip \
    && apk del $PHPIZE_DEPS imagemagick-dev libzip-dev

RUN sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/' /etc/ImageMagick*/policy.xml

# Nginxの設定ファイルをコピー
COPY nginx.conf /etc/nginx/nginx.conf

# ImageMagickのOpenMPスレッド競合による速度低下を防止
ENV MAGICK_THREAD_LIMIT=1

# PHPの実行時間・メモリ・アップロード上限を緩和
RUN { \
    echo 'max_execution_time = 300'; \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
} > /usr/local/etc/php/conf.d/custom.ini

# アプリケーションコードの配置先ディレクトリ
WORKDIR /var/www/html
# COPY html/ /var/www/html/

# 権限の調整
RUN chown -R www-data:www-data /var/www/html \
    && chown -R nginx:nginx /var/lib/nginx

# ポートの公開
EXPOSE 80

# PHP-FPMとNginxを同時に起動するスクリプト
CMD ["sh", "-c", "nginx && php-fpm -F"]