#!/bin/sh

sed -e s/__PORT__/${PORT:-8080}/g /app/config/nginx.conf >/tmp/nginx.conf
php-fpm -R -c /app/config/php.ini -y /app/config/fpm-pool.conf
exec nginx -c /tmp/nginx.conf -g 'daemon off;'
