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

# Cấu hình Apache cơ bản
RUN a2enmod rewrite headers

# Document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Tạo virtual host mẫu (với placeholder port)
RUN echo '<VirtualHost *:${PORT:-8080}>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory "/var/www/html/public">\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY --from=build /app /var/www/html

# Tạo .env tạm nếu cần
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Healthcheck endpoint
RUN echo "<?php http_response_code(200); echo 'OK'; ?>" > public/health.php

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy và cấp quyền cho startup script
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
