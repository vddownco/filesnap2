FROM php:8.2-fpm

# Install system dependencies
RUN apt update && apt install -y \
 git \
 curl \
 libpng-dev \
 libonig-dev \
 libxml2-dev \
 zip \
 unzip \
 libjpeg62-turbo-dev \
 libpng-dev \
 libwebp-dev \
 libfreetype6-dev

# Install PHP extensions
RUN docker-php-ext-configure intl
RUN docker-php-ext-configure opcache
RUN docker-php-ext-configure gd --enable-gd --prefix=/usr --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl opcache
RUN pecl install apcu

# Copy Composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Change current user to www
USER www-data