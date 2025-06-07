FROM composer:2.6 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-apache
WORKDIR /var/www/html

# Cấu hình Apache cho Railway
RUN docker-php-ext-install pdo pdo_mysql opcache \
    && a2enmod rewrite \
    && a2enmod headers

# Document root cho Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Port động cho Railway
RUN echo "Listen \${PORT:-8080}" > /etc/apache2/ports.conf
COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=build /app /var/www/html
COPY .env.railway .env  # Nhớ tạo file .env.railway

# Quyền file
RUN chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link \
    && php artisan optimize:clear

# Healthcheck endpoint
RUN echo "<?php http_response_code(200); echo 'OK'; ?>" > public/health.php

CMD ["apache2-foreground"]
