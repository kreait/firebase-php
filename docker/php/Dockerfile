ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-cli

RUN printf 'tzdata tzdata/Areas select Europe\ntzdata tzdata/Zones/Europe select Zurich\n' | debconf-set-selections; \
    rm /etc/timezone /etc/localtime; \
    dpkg-reconfigure -f noninteractive tzdata

# Install/configure PHP extensions
COPY docker/php/extensions.sh /usr/local/bin/
RUN /usr/local/bin/extensions.sh

# Use the default development PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN apt-get update && apt-get install -y \
    openssl \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# Install Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Install Composer
COPY docker/php/composer.sh /usr/local/bin/
RUN /usr/local/bin/composer.sh

WORKDIR /app
