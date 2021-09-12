FROM php:8-apache  
EXPOSE 80
ENTRYPOINT [ "docker-entrypoint.sh" ]

COPY docker-entrypoint.sh /usr/local/bin/

RUN set -ex && docker-php-ext-install pdo_mysql \
  && ln -s /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY --chown=www-data:www-data ./src/ /var/www/html/
