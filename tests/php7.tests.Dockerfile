FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    curl \
    wget \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng12-dev \
    libpq-dev \
    libxml2-dev \
    git

# Socket dir
RUN mkdir /run/php && chown www-data:www-data /run/php

# Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Extensions
RUN docker-php-ext-install \
    mcrypt \
    zip \
    iconv \
    mysqli \
    soap \
    sockets

#COPY php.ini /usr/local/etc/php/php.ini
#COPY docker-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown -R www-data:www-data /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT: -1
ENV PHP_IDE_CONFIG="serverName=bitrix"

EXPOSE 9000
EXPOSE 8080

WORKDIR /var/www/bitrix
