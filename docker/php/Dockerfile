FROM php:8.3-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql opcache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /app

COPY . /app

RUN chown -R www-data:www-data /app

EXPOSE 9000

CMD ["php-fpm"]
