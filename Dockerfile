FROM alpine/git as preprocess

COPY .git /.git

RUN cd / && git rev-parse --short HEAD > /HEAD

FROM php:8-apache

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive
ENV NODE_OPTIONS='--use-openssl-ca'

# Configure apt and install packages
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils zip unzip nano ncdu 2>&1 \
    #
    # Install git, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git iproute2 procps lsb-release \
    && apt-get -y install mariadb-client \
\
	# Configuring PHP
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && touch "/usr/local/etc/php/conf.d/custom.ini" \
    && echo "memory_limit = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini \
\
    # Install extensions (optional)
    && docker-php-ext-install pdo_mysql \
\
    # Install composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    # Enable URL rewriting using .htaccess
    && a2enmod rewrite \
\
    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/KeepAliveTimeout 5/KeepAliveTimeout 10/' /etc/apache2/apache2.conf

COPY --chown=www-data:www-data ./src/ /var/www/html/
COPY composer.json /var/www/
RUN composer install --working-dir=/var/www/

RUN mkdir /var/www/.git/
COPY --from=preprocess /HEAD /var/www/.git/

WORKDIR /var/www

# data folder for log, import, files
RUN mkdir -p /usr/local/share/hashtopolis
RUN mkdir /usr/local/share/hashtopolis/log /usr/local/share/hashtopolis/import /usr/local/share/hashtopolis/files
RUN chown -R www-data:www-data /usr/local/share/hashtopolis

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=dialog
COPY docker-entrypoint.sh /usr/local/bin
ENTRYPOINT [ "docker-entrypoint.sh" ]
