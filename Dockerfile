FROM alpine/git as preprocess

COPY .gi[t] /.git

RUN cd / && git rev-parse --short HEAD > /HEAD; exit 0

# BASE image
# ----BEGIN----
FROM php:8-apache as hashtopolis-server-base

# Enable possible build args for injecting user commands
ARG CONTAINER_USER_CMD_PRE
ARG CONTAINER_USER_CMD_POST

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive
ENV NODE_OPTIONS='--use-openssl-ca'

# Set default hashtopolis env
ENV HASHTOPOLIS_DOCUMENT_ROOT=/var/www/html/src
ENV HASHTOPOLIS_PATH=/usr/local/share/hashtopolis
ENV HASHTOPOLIS_FILES_PATH=${HASHTOPOLIS_PATH}/files
ENV HASHTOPOLIS_IMPORT_PATH=${HASHTOPOLIS_PATH}/import
ENV HASHTOPOLIS_LOG_PATH=${HASHTOPOLIS_PATH}/log
ENV HASHTOPOLIS_CONFIG_PATH=${HASHTOPOLIS_PATH}/config

# Add support for TLS inspection corporate setups, see .env.sample for details
ENV NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt 

# Check for and run optional user-supplied command to enable (advanced) customizations of the container
RUN if [ -n "${CONTAINER_USER_CMD_PRE}" ]; then echo "${CONTAINER_USER_CMD_PRE}" | sh ; fi

# Configure apt and install packages
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils zip unzip nano ncdu gettext-base 2>&1 \
    #
    # Install git, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git iproute2 procps lsb-release \
    && apt-get -y install mariadb-client \
    && apt-get -y install libpng-dev \
\
    # Install extensions (optional)
    && docker-php-ext-install pdo_mysql gd \
\
    # Install composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    # Enable URL rewriting using .htaccess
    && a2enmod rewrite

RUN sed -i 's/KeepAliveTimeout 5/KeepAliveTimeout 10/' /etc/apache2/apache2.conf

RUN mkdir -p ${HASHTOPOLIS_DOCUMENT_ROOT} \
    && mkdir ${HASHTOPOLIS_DOCUMENT_ROOT}/../../.git/ \
    && mkdir -p ${HASHTOPOLIS_PATH} \
    && chown www-data:www-data ${HASHTOPOLIS_PATH} \
    && chmod g+w ${HASHTOPOLIS_PATH} \
    && mkdir -p ${HASHTOPOLIS_FILES_PATH} \
    && chown www-data:www-data ${HASHTOPOLIS_FILES_PATH} \
    && chmod g+w ${HASHTOPOLIS_FILES_PATH} \
    && mkdir -p ${HASHTOPOLIS_IMPORT_PATH} \
    && chown www-data:www-data ${HASHTOPOLIS_IMPORT_PATH} \
    && chmod g+w ${HASHTOPOLIS_IMPORT_PATH} \
    && mkdir -p ${HASHTOPOLIS_LOG_PATH} \
    && chown www-data:www-data ${HASHTOPOLIS_LOG_PATH} \
    && chmod g+w ${HASHTOPOLIS_LOG_PATH} \
    && mkdir -p ${HASHTOPOLIS_CONFIG_PATH} \
    && chown www-data:www-data ${HASHTOPOLIS_CONFIG_PATH} \
    && chmod g+w ${HASHTOPOLIS_CONFIG_PATH}

COPY --from=preprocess /HEA[D] ${HASHTOPOLIS_DOCUMENT_ROOT}/../../.git/

COPY composer.json ${HASHTOPOLIS_DOCUMENT_ROOT}/../
RUN composer install --working-dir=${HASHTOPOLIS_DOCUMENT_ROOT}/..

ENV DEBIAN_FRONTEND=dialog
COPY docker-entrypoint.sh /usr/local/bin

# Setting the hashtopolis document root is done at build time. Because the www-data user cannot write to the apache config folder.
COPY 000-default.conf /tmp/
RUN envsubst '${HASHTOPOLIS_DOCUMENT_ROOT}' < /tmp/000-default.conf > /etc/apache2/sites-available/000-default.conf && rm /tmp/000-default.conf

ENTRYPOINT [ "docker-entrypoint.sh" ]
# ----END----


# PRODUCTION Image
# ----BEGIN----
FROM hashtopolis-server-base as hashtopolis-server-prod

COPY --chown=www-data:www-data ./src/ $HASHTOPOLIS_DOCUMENT_ROOT

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && touch "/usr/local/etc/php/conf.d/custom.ini" \
    && echo "memory_limit = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini \
    \
    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

USER www-data
# ----END----


# DEVELOPMENT Image
# ----BEGIN----
FROM hashtopolis-server-base as hashtopolis-server-dev

# Setting up development requirements, install xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.client_port = 9003" >> /usr/local/etc/php/conf.d/xdebug.ini \
    \
    # Configuring PHP
    && touch "/usr/local/etc/php/conf.d/custom.ini" \
	&& echo "display_errors = on" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "memory_limit = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "upload_max_filesize = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "error_log = /dev/stderr" >> /usr/local/etc/php/conf.d/custom.ini

# Install python (unittests)
RUN apt-get update \
    && apt-get install -y python3 python3-pip python3-requests python3-pytest

#TODO: Should source from ./ci/apiv2/requirements.txt
RUN pip3 install confidence tuspy --break-system-packages

# Clean up
RUN apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*


# Adding VSCode user and fixing permissions
RUN groupadd vscode && useradd -rm -d /var/www -s /bin/bash -g vscode -G www-data -u 1001 vscode \
    && chown -R www-data:www-data /var/www \
    && chmod -R g+w /var/www

# This is a seperate step so that changes to the code do not cause the container to be rebuild
# And it will be ran last
COPY --chown=www-data:www-data . ${HASHTOPOLIS_DOCUMENT_ROOT}/..

USER vscode
# ----END----
