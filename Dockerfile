# Build stage
FROM composer:2.6 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Production stage
FROM php:8.2-apache
WORKDIR /var/www/html

# Cài đặt extensions PHP
RUN docker-php-ext-install pdo pdo_mysql

# Sao chép code từ build stage
COPY --from=build /app /var/www/html
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Cấu hình Apache
COPY public public
RUN chown -R www-data:www-data /var/www/html/storage \
    && a2enmod rewrite

# Port và lệnh khởi động
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
