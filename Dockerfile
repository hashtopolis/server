FROM php:8-apache  
EXPOSE 80
ENTRYPOINT [ "docker-entrypoint.sh" ]

COPY docker-entrypoint.sh /usr/local/bin/

RUN apt-get update && apt-get install -y libpng-dev

RUN set -ex && docker-php-ext-install pdo_mysql gd

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
    touch "$PHP_INI_DIR/conf.d/custom.ini"; \
    echo "memory_limit = 256m"; \
    echo "upload_max_filesize = 256m"; \
    echo "max_execution_time = 60"; \
} > "$PHP_INI_DIR/conf.d/custom.ini"

# workaround for the copy of git HEAD, as when called without ./build.sh, this file does not exist

RUN mkdir -p /var/www/.git

COPY LICENSE.txt HEAD* /var/www/.git/

RUN rm /var/www/.git/LICENSE.txt

COPY --chown=www-data:www-data ./src/ /var/www/html/
