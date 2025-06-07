FROM composer:2.6 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-apache
WORKDIR /var/www/html

# Tối ưu hóa cho Railway
RUN docker-php-ext-install pdo pdo_mysql opcache \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY --from=build /app /var/www/html
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Fix permission và caching
RUN chown -R www-data:www-data . \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

EXPOSE 8080
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT}"]
CMD ["sh", "-c", "/usr/local/bin/wait-for-db.sh && php artisan serve --host=0.0.0.0 --port=${PORT}"]
