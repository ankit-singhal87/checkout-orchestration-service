ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} AS composer_binary

FROM php:8.5-cli-bookworm

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
  && docker-php-ext-install \
    intl \
    mbstring \
    pcntl \
    pdo_mysql \
    sockets \
    zip \
  && pecl install redis xdebug \
  && docker-php-ext-enable redis xdebug \
  && { \
    echo "zend_extension=xdebug"; \
    echo "xdebug.mode=off"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
    echo "xdebug.start_with_request=trigger"; \
  } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer_binary /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "-v"]
