FROM composer:2.6 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-apache
WORKDIR /var/www/html

# Cấu hình Apache cho Railway
RUN docker-php-ext-install pdo pdo_mysql opcache \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Quan trọng: Cấu hình document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=build /app /var/www/html
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Sửa quyền đúng cách (Railway yêu cầu)
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && php artisan storage:link

# Cấu hình port động cho Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN echo "Listen ${PORT:-8080}" > /etc/apache2/ports.conf

# Healthcheck endpoint đơn giản
RUN echo "<?php http_response_code(200); echo 'OK'; ?>" > public/health.php

CMD ["sh", "-c", "docker-php-entrypoint apache2-foreground"]
