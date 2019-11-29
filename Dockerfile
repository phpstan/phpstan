FROM php:7.4-cli-alpine

RUN php --version

ENV COMPOSER_HOME /composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV PATH /composer/vendor/bin:$PATH
ENV PHP_CONF_DIR=/usr/local/etc/php/conf.d

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=-1" > $PHP_CONF_DIR/99_memory-limit.ini \
    && apk add git \
    && rm -rf /var/cache/apk/* /var/tmp/* /tmp/*

ARG PHPSTAN_VERSION

RUN composer global require phpstan/phpstan:"$PHPSTAN_VERSION"

VOLUME ["/app"]
WORKDIR /app

ENTRYPOINT ["phpstan"]
