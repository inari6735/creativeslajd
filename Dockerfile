# Dockerfile dla CreativeSlajd - Production Ready
FROM php:8.4-fpm-alpine AS base

# Instalacja zależności systemowych
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    postgresql-dev \
    oniguruma-dev

# Instalacja i konfiguracja rozszerzeń PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        gd \
        intl \
        zip \
        opcache \
        mbstring

# Konfiguracja opcache dla produkcji
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.interned_strings_buffer=16'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Konfiguracja PHP dla produkcji
RUN { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=10M'; \
        echo 'post_max_size=10M'; \
        echo 'max_execution_time=300'; \
        echo 'expose_php=Off'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/var/log/php/error.log'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Instalacja Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ===== BUILDER STAGE =====
FROM base AS builder

# Kopiowanie plików zależności
COPY composer.json composer.lock symfony.lock ./
COPY bin bin/

# Instalacja zależności PHP (bez dev)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Kopiowanie plików aplikacji
COPY . .

# Generowanie autoloadera i optymalizacja
RUN composer dump-autoload --no-dev --classmap-authoritative

# ===== PRODUCTION STAGE =====
FROM base AS production

# Tworzenie użytkownika www-data z właściwymi uprawnieniami
RUN addgroup -g 1000 www-data || true \
    && adduser -D -u 1000 -G www-data www-data || true

# Utworzenie katalogów
RUN mkdir -p /var/www/html/var/cache \
             /var/www/html/var/log \
             /var/www/html/public/uploads/slides \
             /var/log/php \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/log/php

# Kopiowanie plików z buildera
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

WORKDIR /var/www/html

# Uprawnienia do katalogów writable
RUN chmod -R 775 var public/uploads

# Używamy www-data
USER www-data

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm -t || exit 1

EXPOSE 9000

CMD ["php-fpm"]
