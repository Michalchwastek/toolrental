# Użyj oficjalnego obrazu PHP jako bazowego
FROM php:8.2.11-fpm-alpine3.18

# Zainstaluj wymagane rozszerzenia systemowe i rozszerzenia PHP dla PostgreSQL
# docker-php-ext-install to skrypt pomocniczy do instalowania rozszerzeń PHP
RUN apk add --no-cache libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql