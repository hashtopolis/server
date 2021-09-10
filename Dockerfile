FROM php:8-apache  
RUN set -ex && bash -c "set -euxo pipefail && install <(curl -fsSLo - "$(curl -sLI -o /dev/null -w '%{url_effective}' "https://github.com/tianon/gosu/releases/latest" | sed 's/tag/download/')/gosu-amd64") /usr/local/bin/gosu" && gosu --version
EXPOSE 80
ENTRYPOINT [ "docker-entrypoint.sh" ]

COPY docker-entrypoint.sh /usr/local/bin/

RUN set -ex && docker-php-ext-install pdo_mysql
COPY --chown=www-data:www-data ./src/ /var/www/html/
