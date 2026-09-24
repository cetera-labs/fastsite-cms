# PHP-FPM для CI: публичный образ вместо registry.cetera.su, набор расширений — как в dev-образе.
# Используется через dev/docker-compose.ci.yml, см. .github/workflows/tests.yml
ARG PHP_VERSION=8.4-fpm
FROM php:${PHP_VERSION}

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        bcmath calendar exif gd gettext intl mbstring memcached mysqli opcache \
        pdo_mysql soap sodium xsl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/site
