# ===================================
# Multi-stage Dockerfile for Symfony
# ===================================

# Stage 1: Base PHP Image with Extensions
FROM php:8.4-fpm-alpine AS base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        intl \
        zip \
        opcache \
        bcmath \
        gd \
        exif \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ===================================
# Stage 2: Development
# ===================================
FROM base AS development

# Install Xdebug for development
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS

# Xdebug configuration
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# PHP configuration for development
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/custom.ini

# ===================================
# Stage 3: Production
# ===================================
FROM base AS production

# PHP configuration for production
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY app /var/www/html

# Install dependencies (production only, optimized)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/var \
    && mkdir -p /var/www/html/public/media \
    && chown -R www-data:www-data /var/www/html/public/media

# Generate JWT keys if they don't exist (optional, better to do in entrypoint)
RUN if [ ! -f config/jwt/private.pem ]; then \
        mkdir -p config/jwt && \
        php bin/console lexik:jwt:generate-keypair --skip-if-exists || true; \
    fi

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm -t || exit 1

EXPOSE 9000

CMD ["php-fpm"]

