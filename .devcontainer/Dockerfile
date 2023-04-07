FROM php:8-apache  

ARG DEV_CONTAINER_USER_CMD_PRE
ARG DEV_CONTAINER_USER_CMD_POST

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive

## Add support for TLS inspection corporate setups, see .env.sample for details
ENV NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt 

# Check for and run optional user-supplied command to enable (advanced) customizations of the dev container
RUN if [ -n "${DEV_CONTAINER_USER_CMD_PRE}" ]; then echo "${DEV_CONTAINER_USER_CMD_PRE}" | sh ; fi

# Configure apt and install packages
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils zip unzip nano ncdu 2>&1 \
    #
    # Install git, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git iproute2 procps lsb-release \
    && apt-get -y install mariadb-client \

    # Install xdebug
    && yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
	&& echo "xdebug.client_port = 9003" >> /usr/local/etc/php/conf.d/xdebug.ini \

	# Configuring PHP
    && touch "/usr/local/etc/php/conf.d/custom.ini" \
	&& echo "display_errors = on" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "memory_limit = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "upload_max_filesize = 256m" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini \
	&& echo "error_log = /dev/stderr" >> /usr/local/etc/php/conf.d/custom.ini \

	# Install extensions (optional)
	&& docker-php-ext-install pdo_mysql \

    # Install composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    # Enable URL rewriting using .htaccess
    && a2enmod rewrite \

    # Install python (unittests)
    && apt-get install -y python3 python3-pip \
    # TODO: Should source from ./ci/apiv2/requirements.txt
    && pip3 install requests pytest confidence tuspy \

    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* \

#
# Link our site config to the devcontainer
RUN rm -rf /etc/apache2/sites-enabled \
	&& ln -s /var/www/html/.devcontainer/sites-enabled /etc/apache2/sites-enabled

# Adding VSCode user and fixing permissions
RUN groupadd vscode && useradd -rm -d /var/www -s /bin/bash -g vscode -G www-data -u 1001 vscode \
    && chown -R www-data:www-data /var/www \
    && chmod g+w /var/www

# Check for and run optional user-supplied command to enable (advanced) customizations of the dev container
RUN if [ -n "${DEV_CONTAINER_USER_CMD_POST}" ]; then echo "${DEV_CONTAINER_USER_CMD_POST}" | sh ; fi

# Switching to vscode user and working dir
WORKDIR /var/www
user vscode

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=dialog
COPY docker-entrypoint.sh /usr/local/bin
ENTRYPOINT [ "docker-entrypoint.sh" ]
