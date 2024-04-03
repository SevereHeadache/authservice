#!/bin/sh

composer install && \
touch logs/app.log && \
chown www-data:www-data logs/app.log && \
php-fpm
