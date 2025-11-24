# Base image PHP-FPM Alpine (lebih kecil)
FROM php:8.3-fpm-alpine AS base

# Install system dependencies using apk
# Kita juga menginstall build-deps sebagai virtual package agar bisa dihapus setelah selesai
RUN apk add --no-cache \
    # Persistent dependencies\
    git \
    unzip \
    libzip \
    libpng \
    jpeg \
    libwebp \
    libxml2 \
    nodejs \
    npm \
    mariadb-client \
    && apk add --no-cache --virtual .build-deps \
    # Build-time dependencies
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    libxml2-dev \
    libwebp-dev \
    # Install PHP extensions
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip gd exif bcmath \
    # Clean up build dependencies
    && apk del .build-deps

# Set working directory
WORKDIR /app

# --- Create a non-root user ---
ARG UID=1000
ARG GID=1000
RUN addgroup -g $GID zen && adduser -u $UID -G zen -s /bin/sh -D zen

# --- NPM Build Stage ---
FROM base AS npm_stage
COPY package.json package-lock.json ./
# Hapus node_modules jika ada dari host untuk memastikan clean install
RUN rm -rf node_modules && npm install

# --- Composer Build Stage ---
FROM base AS composer_stage
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist
RUN chown -R www-data:www-data vendor

# --- Final Application Stage ---
FROM base AS final_app

# Copy composer executable
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer

# Copy dependencies from previous stages
COPY --from=composer_stage /app/vendor/ /app/vendor/
COPY --from=npm_stage /app/node_modules/ /app/node_modules/

# Copy application code
COPY . .

# Re-create storage directories since they are in .dockerignore
RUN mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache storage/logs storage/app/public storage/app/temp_restores

# Set permissions for Laravel storage and cache (user www-data juga di alpine image)
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000