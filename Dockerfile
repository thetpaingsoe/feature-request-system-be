# Base stage (common for both environments)
FROM php:8.2-apache AS base

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
RUN apt-get update && apt-get install -y \
    git zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev \
    npm nodejs \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Enable Apache Rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel app
COPY . .
RUN rm -rf node_modules
RUN rm -rf public/build

# Install PHP dependencies via Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

FROM base AS local

RUN npm install 

EXPOSE 8000

FROM base AS builder

# Install Node.js dependencies and build assets
RUN npm install && npm run build

EXPOSE 8000