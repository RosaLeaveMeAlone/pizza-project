# Used for prod build.
FROM php:8.3-fpm as php

ARG user
ARG uid

# Set environment variables
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_REVALIDATE_FREQ=0

# Install dependencies including SQLite support
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \         
    libicu-dev \
    pkg-config \
    sqlite3 \
    libsqlite3-dev

# Install PHP extensions including SQLite support
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd intl zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer executable.
COPY --from=composer:2.6.6 /usr/bin/composer /usr/bin/composer

RUN useradd -u $uid -ms /bin/bash -g www-data $user

# Copy configuration files.
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy entrypoint script and set permissions.
COPY ./docker/php/entrypoint.sh /var/www/html/docker/php/entrypoint.sh
RUN chown $user:www-data /var/www/html/docker/php/entrypoint.sh
RUN chmod +x /var/www/html/docker/php/entrypoint.sh

# Set working directory to /var/www.
WORKDIR /var/www/html

# Copy files from current folder to container current folder (set in workdir).
COPY . /var/www/html
COPY --chown=$user:www-data . /var/www/html

# Create laravel caching folders.
RUN mkdir -p /var/www/html/storage/framework
RUN mkdir -p /var/www/html/storage/framework/cache
RUN mkdir -p /var/www/html/storage/framework/testing
RUN mkdir -p /var/www/html/storage/framework/sessions
RUN mkdir -p /var/www/html/storage/framework/views

# Create database directory
RUN mkdir -p /var/www/html/database

# Fix files ownership.
RUN chown -R $user:www-data /var/www/html/storage
RUN chown -R $user:www-data /var/www/html/storage/framework
RUN chown -R $user:www-data /var/www/html/storage/framework/sessions
RUN chown -R $user:www-data /var/www/html/database

# Set correct permission.
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage/logs
RUN chmod -R 775 /var/www/html/storage/framework
RUN chmod -R 775 /var/www/html/storage/framework/sessions
RUN chmod -R 775 /var/www/html/database

USER $user

EXPOSE 9000

# Run the entrypoint file.
ENTRYPOINT ["/var/www/html/docker/php/entrypoint.sh"]