FROM php:8-apache  
EXPOSE 80
ENTRYPOINT [ "docker-entrypoint.sh" ]

COPY docker-entrypoint.sh /usr/local/bin/

RUN set -ex && docker-php-ext-install pdo_mysql 

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
    touch "$PHP_INI_DIR/conf.d/custom.ini"; \
    echo "memory_limit = 256m"; \
    echo "upload_max_filesize = 256m"; \
    echo "max_execution_time = 60"; \
} > "$PHP_INI_DIR/conf.d/custom.ini"

COPY --chown=www-data:www-data ./src/ /var/www/html/
