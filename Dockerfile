FROM composer:2.6 AS build
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-apache
WORKDIR /var/www/html

# Cài đặt dependencies hệ thống
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql opcache

# Cấu hình Apache
RUN a2enmod rewrite headers \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Tạo file cấu hình Apache trực tiếp
RUN echo "Listen \${PORT:-8080}" > /etc/apache2/ports.conf && \
    echo '<VirtualHost *:${PORT:-8080}>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory "/var/www/html/public">\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY --from=build /app /var/www/html

# Tạo .env tạm nếu cần
RUN if [ ! -f .env ]; then \
        cp .env.example .env; \
        php artisan key:generate; \
    fi

# Healthcheck endpoint
RUN echo "<?php http_response_code(200); echo 'OK'; ?>" > public/health.php

# Đặt lệnh artisan ở CUỐI CÙNG sau khi mọi thứ đã sẵn sàng
RUN chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan view:cache

CMD ["apache2-foreground"]
