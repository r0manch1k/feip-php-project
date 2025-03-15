FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    wget \
    zlib1g-dev \
    libmcrypt-dev \
    libzip-dev \
    librabbitmq-dev

RUN docker-php-ext-install pdo pdo_pgsql \
    sockets \
    zip

RUN pecl channel-update pecl.php.net && pecl install amqp && pecl install xdebug-3.4.1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/project

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

EXPOSE 9000

CMD ["php-fpm"]
