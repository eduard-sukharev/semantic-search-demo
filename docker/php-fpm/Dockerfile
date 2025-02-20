FROM php:8.4-fpm-bullseye as base

RUN apt update \
    && apt install -y git unzip  \
    libcurl4-openssl-dev libpq-dev libz-dev libicu-dev libonig-dev

RUN docker-php-ext-install -j$(nproc) curl pdo pgsql pdo_pgsql dba intl mbstring

RUN pecl install xdebug-3.4.1 \
    && pecl install \
    && docker-php-ext-enable xdebug

ARG SYMFONY_CLI_VERSION=5.10.6
RUN curl -sSLk https://get.symfony.com/cli/installer | bash
COPY docker/php-fpm/symfony.ini /usr/local/etc/php/conf.d/
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/
COPY docker/php-fpm/zz-docker.conf /usr/local/etc/php-fpm.d/

RUN ln -s /usr/local/bin/php /usr/bin/php

ARG PROJECT_DIR=/app
RUN mkdir -p $PROJECT_DIR

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR $PROJECT_DIR

# RUN mkdir /var/www/.ssh && \
#    echo "Host private.gitlab.repo\n\tStrictHostKeyChecking no\n" > /var/www/.ssh/config

FROM base as dev

RUN chown -R www-data:www-data $PROJECT_DIR
RUN chown -R www-data:www-data /var/www

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupmod -g $GROUP_ID www-data
RUN usermod -u $USER_ID -g $GROUP_ID www-data

USER www-data
