FROM php:8-apache  

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive

# Configure apt and install packages
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils zip unzip nano ncdu 2>&1 \
	#
	# Webgrind prereqs
	# && apt-get -y install --no-install-recommends python graphviz 2>&1 \
    #
    # Install git, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git iproute2 procps lsb-release \
    && apt-get -y install mariadb-client \
    #
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

	#
	# Install extensions (optional)
	# && apt-get -y install libicu-dev \
	# && docker-php-ext-install intl pdo_mysql opcache \
	&& docker-php-ext-install pdo_mysql \

	# && pecl install apcu && docker-php-ext-enable apcu \
	# && echo "apc.enable_cli=1" > /usr/local/etc/php/php.ini \
	# && echo "apc.enable=1" > /usr/local/etc/php/php.ini \
	# && a2enmod rewrite \
	#
	# Install composer
	# && curl -s https://getcomposer.org/installer | php \
	# && mv composer.phar /usr/local/bin/composer \
	# && composer global require hirak/prestissimo \
    #
    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* \

#
# Link our site config to the devcontainer
RUN rm -rf /etc/apache2/sites-enabled \
	&& ln -s /var/www/html/.devcontainer/sites-enabled /etc/apache2/sites-enabled

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=dialog
COPY docker-entrypoint.sh /usr/local/bin
ENTRYPOINT [ "docker-entrypoint.sh" ]
