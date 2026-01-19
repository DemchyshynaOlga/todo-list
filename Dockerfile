ARG composer_version=2.9
FROM composer:${composer_version} AS composer-stage

FROM php:8.3-fpm-alpine

ARG user=testTask
ARG uid=1000

RUN apk add --no-cache \
    git \
    curl \
    nodejs \
    npm \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache

RUN apk del $PHPIZE_DEPS linux-headers

COPY --from=composer-stage /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${uid} ${user} \
    && adduser -G ${user} -u ${uid} -D -h /home/${user} ${user} \
    && addgroup ${user} www-data

WORKDIR /var/www/sample

COPY ./sample/composer.json ./sample/composer.lock* ./sample/package.json ./sample/package-lock.json* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts && \
    npm ci --silent

COPY ./sample ./

RUN php artisan package:discover --ansi || true

RUN npm run build

WORKDIR /var/www

COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/sample
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R ${user}:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

USER ${user}

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]
