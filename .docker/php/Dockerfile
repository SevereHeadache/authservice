FROM php:8.3-fpm-alpine

RUN apk add libpq-dev --no-cache &&\
    docker-php-ext-install -j$(nproc) pgsql pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ADD entrypoint.sh /etc/entrypoint.sh
RUN ["chmod", "+x", "/etc/entrypoint.sh"]

ENTRYPOINT [ "/etc/entrypoint.sh" ]
