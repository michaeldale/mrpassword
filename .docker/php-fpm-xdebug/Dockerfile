FROM php:7.1-fpm-alpine

# Install persistent dependencies
RUN apk add --no-cache --virtual .persistent-deps \
    icu-dev \
    libmcrypt-dev \
    libsodium-dev \
    postgresql-dev

# Install build dependencies
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    gcc \
    libc-dev \
    make

# Build
RUN set -xe \
    && docker-php-ext-install \
        bcmath \
        intl \
        pdo_pgsql \
    && pecl install \
        libsodium \
        redis \
        xdebug \
    && docker-php-ext-enable \
        redis \
        sodium \
        xdebug \
    && apk del .build-deps

COPY etc/php/conf.d/* $PHP_INI_DIR/conf.d/
COPY etc/php/php.ini $PHP_INI_DIR/
COPY etc/php-fpm.d/zz-docker.conf $PHP_INI_DIR/../php-fpm.d/zz-docker.conf

WORKDIR /app
